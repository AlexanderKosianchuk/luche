<?php

namespace Component;

use Exception;

class FlightComponent extends BaseComponent
{
  /**
   * @Inject
   * @var Component\FdrComponent
   */
  private $fdrComponent;

  /**
   * @Inject
   * @var Entity\FlightSettlement
   */
  private $FlightSettlement;

  /**
   * @Inject
   * @var Entity\FlightEvent
   */
  private $FlightEvent;

  /**
   * @Inject
   * @var Entity\FlightEventOld
   */
  private $FlightEventOld;

  /**
   * @Inject
   * @var Entity\CalibrationParam
   */
  private $CalibrationParam;

  public function insert($guid, $flightInfo, $frdId, $userId, $calibrationId = null)
  {
    $user = $this->em()->find('Entity\User', $userId);
    $fdr = $this->em()->find('Entity\Fdr', $frdId);

    $calibration = null;
    if ($calibrationId !== null) {
      $calibration = $this->em()->find('Entity\Calibration', $calibrationId);
    }

    if (!isset($flightInfo['startCopyTime'])) {
      $flightInfo['startCopyTime'] = time();
    }

    if (!isset($flightInfo['performer'])) {
      $flightInfo['performer'] = $user->getLogin();
    }

    $flight = $this->em()->getRepository('Entity\Flight')
      ->insert($guid, $flightInfo, $fdr, $user, $calibration);

    if ($this->member()->isUser()) {
      $creator = $this->user()->getCreator();

      if ($creator) {
        $this->em()->getRepository('Entity\FlightToFolder')
          ->insert(0, $creator->getId(), $flight);
      }
    }

    if ($this->member()->isUser()
      || $this->member()->isLocal()
      || $this->member()->isModerator()
    ) {
      $this->em()->getRepository('Entity\FlightToFolder')
        ->insert(0, $this->user()->getId(), $flight);
    }

    $admins = $this->em()->getRepository('Entity\User')->getAdmins();

    foreach ($admins as $user) {
      $this->em()->getRepository('Entity\FlightToFolder')
        ->insert(0, $user->getId(), $flight);
    }

    return $flight;
  }

  public function deleteFlight($flightId, $userId)
  {
    $parameters = ['flightId' => $flightId];

    $qb = $this->em()->createQueryBuilder();
    $qb->select('f')
      ->from('Entity\FlightToFolder', 'f')
      ->where('f.flightId = :flightId');

    if (!$this->member()->isAdmin()) {
      $qb->andWhere('f.userId = :userId');
      $parameters['userId'] = $userId;
    }

    $qb->setParameters($parameters);
    $flightToFolder = $qb->getQuery()->getArrayResult();

    foreach ($flightToFolder as $item) {
      $flightToFolder = $this->em()->find('Entity\FlightToFolder', $item['id']);
      $this->em()->remove($flightToFolder);
      $this->em()->flush();
    }

    if (!$this->rbac()->check('deleteFlightIrretrievably')) {
      return;
    }

    $flight = $this->em()
      ->getRepository('Entity\Flight')
      ->findOneBy([
        'id' => $flightId
      ]);

    if (!$flight) {
      return true;
    }

    $fdr = $flight->getFdr();
    $guid = $flight->getGuid();

    $analogPrefixes = $this->fdrComponent->getAnalogPrefixes($fdr->getId());
    $binaryPrefixes = $this->fdrComponent->getBinaryPrefixes($fdr->getId());
    $tables = [];
    foreach ($analogPrefixes as $num) {
      $tables[] = $this->fdrComponent->getAnalogTable($guid, $num);
    }

    foreach ($binaryPrefixes as $num) {
      $tables[] = $this->fdrComponent->getBinaryTable($guid, $num);
    }

    $link = $this->connection()->create('flights');
    $tables[] = $this->FlightSettlement::getTable($link, $guid);
    $tables[] = $this->FlightEvent::getTable($link, $guid);
    $tables[] = $this->FlightEventOld::getTable($link, $guid);

    foreach ($tables as $table) {
      if ($table) {
        $this->connection()->drop($table, null, $link);
      }
    }

    $this->connection()->destroy($link);

    $this->em()->remove($flight);
    $this->em()->flush();

    return true;
  }

  public function getFlightEvents($flightId, $flightGuid = '')
  {
    if (!is_int($flightId)) {
      throw new Exception("Incorrect flightId passed. Integer is required. Passed: "
        . json_encode($sections), 1);
    }

    if (!is_string($flightGuid)) {
      throw new Exception("Incorrect flightGuid passed. Integer is required. Passed: "
        . json_encode($sections), 1);
    }

    if ($flightGuid === '') {
      $flight = $this->em()->find('Entity\Flight', $flightId);
      $flightGuid = $flight->getGuid();
    }

    $link = LinkFactory::create();
    $flightEventTable = FlightEvent::getTable($link, $flightGuid);
    $flightSettlementTable = FlightSettlement::getTable($link, $flightGuid);
    LinkFactory::destroy($link);

    if (!isset($flightEventTable)
      || !isset($flightSettlementTable)
    ) {
      return [];
    }

    $this->em()->getClassMetadata('Entity\FlightEvent')->setTableName($flightEventTable);
    $this->em()->getClassMetadata('Entity\FlightSettlement')->setTableName($flightSettlementTable);
    $events = $this->em()->getRepository('Entity\FlightEvent')->findAll();

    return $events;
  }

  public function getFlightSettlements($flightId, $flightGuid = '')
  {
    if (!is_int($flightId)) {
      throw new Exception("Incorrect flight id passed", 1);
    }

    if ($flightGuid === '') {
      $flight = $this->em()->find('Entity\Flight', $flightId);
      $flightGuid = $flight->getGuid();
    }

    $flightEvents = self::getFlightEvents($flightId, $flightGuid);

    $allFlightSettlements = [];
    foreach ($flightEvents as $flightEvent) {
      $flightSettlements = $flightEvent->getFlightSettlements();
      foreach ($flightSettlements as $flightSettlement) {
        $allFlightSettlements[] = $flightSettlement;
      }
    }

    return $allFlightSettlements;
  }

  public function createParamTables($flightUid, $paramCyclo, $binaryCyclo)
  {
    $tables = [
      'params' => [],
      'binary' => [],
    ];

    $link = $this->connection()->create('flights');

    foreach ($paramCyclo as $prefix => $cyclo) {
      $table = $this->fdrComponent->getAnalogTable($flightUid, $prefix);
      $tables['params'][] = $table;

      $query = "CREATE TABLE `".$table."` (`frameNum` MEDIUMINT, `time` BIGINT";

      foreach ($cyclo as $param) {
        $query .= ", `".$param["code"]."` FLOAT(7,2)";
      }

      $query .= ", PRIMARY KEY (`frameNum`, `time`)) " .
          "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB;";

      $stmt = $link->prepare($query);
      $stmt->execute();
      $stmt->close();
    }

    foreach ($binaryCyclo as $prefix => $prefixCyclo) {
      $table = $this->fdrComponent->getBinaryTable($flightUid, $prefix);
      $tables['binary'][] = $table;

      $query = "CREATE TABLE `".$table."` (`frameNum` MEDIUMINT, `time` BIGINT, `code` varchar(255)) " .
        "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB;";
      $stmt = $link->prepare($query);
      $stmt->execute();
      $stmt->close();
    }

    $this->connection()->destroy($link);

    return $tables;
  }

  public function getFlightTiming($flightId)
  {
    $flight = $this->em()->getRepository('Entity\Flight')
      ->findOneBy(['id' => $flightId]);
    $fdr = $flight->getFdr();
    $stepLength = $fdr->getStepLength();

    $prefixArr = $this->fdrComponent->getAnalogPrefixes($fdr->getId());
    $framesCount = $this->getFramesCount($flight->getGuid(), $prefixArr[0]); //giving just some prefix
    $stepsCount = $framesCount * $stepLength;

    return [
      'duration' => $stepsCount,
      'startCopyTime' => $flight->getStartCopyTime(),
      'stepLength' => $stepLength,
      'framesCount' => $framesCount
    ];
  }

  public function getFramesCount($apTableName, $prefix)
  {
    $link = $this->connection()->create('flights');
    $query = "SELECT MAX(`frameNum`) FROM `".$apTableName."_ap_". $prefix ."` LIMIT 1;";
    $result = $link->query($query);
    $row = $result->fetch_array();
    $framesCount = $row[0];
    $this->connection()->destroy($link);

    return $framesCount;
  }
}