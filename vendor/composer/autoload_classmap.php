<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Component\\BaseComponent' => $baseDir . '/back/framework/BaseComponent.php',
    'Component\\CalibrationComponent' => $baseDir . '/back/component/CalibrationComponent.php',
    'Component\\EventProcessingComponent' => $baseDir . '/back/component/EventProcessingComponent.php',
    'Component\\FdrComponent' => $baseDir . '/back/component/FdrComponent.php',
    'Component\\FdrCycloComponent' => $baseDir . '/back/component/FdrCycloComponent.php',
    'Component\\FlightComponent' => $baseDir . '/back/component/FlightComponent.php',
    'Component\\FlightProcessingComponent' => $baseDir . '/back/component/FlightProcessingComponent.php',
    'Component\\FolderComponent' => $baseDir . '/back/component/FolderComponent.php',
    'Component\\FrameComponent' => $baseDir . '/back/component/FrameComponent.php',
    'Component\\OSdetectionComponent' => $baseDir . '/back/component/OSdetectionComponent.php',
    'Component\\Rbac' => $baseDir . '/back/framework/system-components/Rbac.php',
    'Component\\RealConnection' => $baseDir . '/back/framework/system-components/RealConnection.php',
    'Component\\ResponseRegistrator' => $baseDir . '/back/component/ResponseRegistrator.php',
    'Component\\RuntimeManager' => $baseDir . '/back/component/RuntimeManager.php',
    'Component\\TemplateEngine' => $baseDir . '/back/framework/system-components/TemplateEngine.php',
    'Component\\User' => $baseDir . '/back/framework/system-components/User.php',
    'Component\\UserSettingsComponent' => $baseDir . '/back/component/UserSettingsComponent.php',
    'Controller\\BaseController' => $baseDir . '/back/framework/BaseController.php',
    'Controller\\CalibrationController' => $baseDir . '/back/controller/CalibrationController.php',
    'Controller\\ChartController' => $baseDir . '/back/controller/ChartController.php',
    'Controller\\FdrController' => $baseDir . '/back/controller/FdrController.php',
    'Controller\\FlightEventsController' => $baseDir . '/back/controller/FlightEventsController.php',
    'Controller\\FlightsController' => $baseDir . '/back/controller/FlightsController.php',
    'Controller\\FolderController' => $baseDir . '/back/controller/FolderController.php',
    'Controller\\IndexController' => $baseDir . '/back/controller/IndexController.php',
    'Controller\\ResultsController' => $baseDir . '/back/controller/ResultsController.php',
    'Controller\\TemplatesController' => $baseDir . '/back/controller/TemplatesController.php',
    'Controller\\UploaderController' => $baseDir . '/back/controller/UploaderController.php',
    'Controller\\UsersController' => $baseDir . '/back/controller/UsersController.php',
    'EntityTraits\\dynamicTable' => $baseDir . '/back/entity/traits/dynamicTable.php',
    'Entity\\Airport' => $baseDir . '/back/entity/Airport.php',
    'Entity\\Calibration' => $baseDir . '/back/entity/Calibration.php',
    'Entity\\CalibrationParam' => $baseDir . '/back/entity/CalibrationParam.php',
    'Entity\\Event' => $baseDir . '/back/entity/Event.php',
    'Entity\\EventSettlement' => $baseDir . '/back/entity/EventSettlement.php',
    'Entity\\EventToFdr' => $baseDir . '/back/entity/EventToFdr.php',
    'Entity\\Fdr' => $baseDir . '/back/entity/Fdr.php',
    'Entity\\FdrAnalogParam' => $baseDir . '/back/entity/FdrAnalogParam.php',
    'Entity\\FdrBinaryParam' => $baseDir . '/back/entity/FdrBinaryParam.php',
    'Entity\\FdrToUser' => $baseDir . '/back/entity/FdrToUser.php',
    'Entity\\Flight' => $baseDir . '/back/entity/Flight.php',
    'Entity\\FlightComments' => $baseDir . '/back/entity/FlightComment.php',
    'Entity\\FlightEvent' => $baseDir . '/back/entity/FlightEvent.php',
    'Entity\\FlightEventOld' => $baseDir . '/back/entity/FlightEventOld.php',
    'Entity\\FlightSettlement' => $baseDir . '/back/entity/FlightSettlement.php',
    'Entity\\FlightToFolder' => $baseDir . '/back/entity/FlightToFolder.php',
    'Entity\\Folder' => $baseDir . '/back/entity/Folder.php',
    'Entity\\User' => $baseDir . '/back/entity/User.php',
    'Entity\\UserActivity' => $baseDir . '/back/entity/UserActivity.php',
    'Entity\\UserAuth' => $baseDir . '/back/entity/UserAuth.php',
    'Entity\\UserSetting' => $baseDir . '/back/entity/UserSetting.php',
    'Exception\\BadRequestException' => $baseDir . '/back/exception/BadRequestException.php',
    'Exception\\BaseException' => $baseDir . '/back/exception/BaseException.php',
    'Exception\\ForbiddenException' => $baseDir . '/back/exception/ForbiddenException.php',
    'Exception\\NotFoundException' => $baseDir . '/back/exception/NotFoundException.php',
    'Exception\\UnauthorizedException' => $baseDir . '/back/exception/UnauthorizedException.php',
    'Exception\\UnknownActionException' => $baseDir . '/back/exception/UnknownActionException.php',
    'Framework\\Application' => $baseDir . '/back/framework/Application.php',
    'Model\\Calibration' => $baseDir . '/back/model/Calibration.php',
    'Model\\Channel' => $baseDir . '/back/model/Channel.php',
    'Model\\DataBaseConnector' => $baseDir . '/back/model/DataBaseConnector.php',
    'Model\\Fdr' => $baseDir . '/back/model/Fdr.php',
    'Model\\Flight' => $baseDir . '/back/model/Flight.php',
    'Model\\FlightComments' => $baseDir . '/back/model/FlightComments.php',
    'Model\\FlightException' => $baseDir . '/back/model/FlightException.php',
    'Model\\FlightTemplate' => $baseDir . '/back/model/FlightTemplate.php',
    'Model\\Frame' => $baseDir . '/back/model/Frame.php',
    'Repository\\AirportRepository' => $baseDir . '/back/repository/AirportRepository.php',
    'Repository\\CalibrationRepository' => $baseDir . '/back/repository/CalibrationRepository.php',
    'Repository\\FdrToUserRepository' => $baseDir . '/back/repository/FdrToUserRepository.php',
    'Repository\\FlightEventRepository' => $baseDir . '/back/repository/FlightEventRepository.php',
    'Repository\\FlightRepository' => $baseDir . '/back/repository/FlightRepository.php',
    'Repository\\FlightToFolderRepository' => $baseDir . '/back/repository/FlightToFolderRepository.php',
    'Repository\\FolderRepository' => $baseDir . '/back/repository/FolderRepository.php',
    'Repository\\UserRepository' => $baseDir . '/back/repository/UserRepository.php',
);
