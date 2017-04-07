<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit957bae1b0e3e2bc3fe568f00ac4778eb
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Component\\Yaml\\' => 23,
            'Symfony\\Component\\Filesystem\\' => 29,
            'Symfony\\Component\\Debug\\' => 24,
            'Symfony\\Component\\Console\\' => 26,
            'Symfony\\Component\\Config\\' => 25,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'Phinx\\' => 6,
        ),
        'D' => 
        array (
            'Doctrine\\Common\\Cache\\' => 22,
            'Doctrine\\Common\\Annotations\\' => 28,
            'Doctrine\\Common\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Component\\Yaml\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/yaml',
        ),
        'Symfony\\Component\\Filesystem\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/filesystem',
        ),
        'Symfony\\Component\\Debug\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/debug',
        ),
        'Symfony\\Component\\Console\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/console',
        ),
        'Symfony\\Component\\Config\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/config',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Phinx\\' => 
        array (
            0 => __DIR__ . '/..' . '/robmorgan/phinx/src/Phinx',
        ),
        'Doctrine\\Common\\Cache\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/cache/lib/Doctrine/Common/Cache',
        ),
        'Doctrine\\Common\\Annotations\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/annotations/lib/Doctrine/Common/Annotations',
        ),
        'Doctrine\\Common\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/common/lib/Doctrine/Common',
        ),
    );

    public static $prefixesPsr0 = array (
        'E' => 
        array (
            'Evenement' => 
            array (
                0 => __DIR__ . '/..' . '/evenement/evenement/src',
            ),
        ),
        'D' => 
        array (
            'Doctrine\\ORM\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/orm/lib',
            ),
            'Doctrine\\DBAL\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/dbal/lib',
            ),
            'Doctrine\\Common\\Lexer\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/lexer/lib',
            ),
            'Doctrine\\Common\\Inflector\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/inflector/lib',
            ),
            'Doctrine\\Common\\Collections\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/collections/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Component\\EntityManagerComponent' => __DIR__ . '/../..' . '/back/component/EntityManagerComponent.php',
        'Component\\EventProcessingComponent' => __DIR__ . '/../..' . '/back/component/EventProcessingComponent.php',
        'Component\\FdrComponent' => __DIR__ . '/../..' . '/back/component/FdrComponent.php',
        'Component\\FdrCycloComponent' => __DIR__ . '/../..' . '/back/component/FdrCycloComponent.php',
        'Component\\FlightComponent' => __DIR__ . '/../..' . '/back/component/FlightComponent.php',
        'Component\\OSdetectionComponent' => __DIR__ . '/../..' . '/back/component/OSdetectionComponent.php',
        'Component\\RealConnectionFactory' => __DIR__ . '/../..' . '/back/component/RealConnectionComponent.php',
        'Controller\\CController' => __DIR__ . '/../..' . '/back/controller/CController.php',
        'Controller\\CalibrationController' => __DIR__ . '/../..' . '/back/controller/CalibrationController.php',
        'Controller\\ChartController' => __DIR__ . '/../..' . '/back/controller/ChartController.php',
        'Controller\\EntryController' => __DIR__ . '/../..' . '/back/controller/EntryController.php',
        'Controller\\FdrController' => __DIR__ . '/../..' . '/back/controller/FdrController.php',
        'Controller\\FlightsController' => __DIR__ . '/../..' . '/back/controller/FlightsController.php',
        'Controller\\IndexController' => __DIR__ . '/../..' . '/back/controller/IndexController.php',
        'Controller\\PrinterController' => __DIR__ . '/../..' . '/back/controller/PrinterController.php',
        'Controller\\ResultsController' => __DIR__ . '/../..' . '/back/controller/ResultsController.php',
        'Controller\\SearchFlightController' => __DIR__ . '/../..' . '/back/controller/SearchFlightController.php',
        'Controller\\UploaderController' => __DIR__ . '/../..' . '/back/controller/UploaderController.php',
        'Controller\\UserController' => __DIR__ . '/../..' . '/back/controller/UserController.php',
        'Controller\\ViewOptionsController' => __DIR__ . '/../..' . '/back/controller/ViewOptionsController.php',
        'Entity\\Airport' => __DIR__ . '/../..' . '/back/entity/Airport.php',
        'Entity\\Calibration' => __DIR__ . '/../..' . '/back/entity/Calibration.php',
        'Entity\\Event' => __DIR__ . '/../..' . '/back/entity/Event.php',
        'Entity\\EventSettlement' => __DIR__ . '/../..' . '/back/entity/EventSettlement.php',
        'Entity\\EventToFdr' => __DIR__ . '/../..' . '/back/entity/EventToFdr.php',
        'Entity\\Fdr' => __DIR__ . '/../..' . '/back/entity/Fdr.php',
        'Entity\\FdrToUser' => __DIR__ . '/../..' . '/back/entity/FdrToUser.php',
        'Entity\\Flight' => __DIR__ . '/../..' . '/back/entity/Flight.php',
        'Entity\\FlightComments' => __DIR__ . '/../..' . '/back/entity/FlightComment.php',
        'Entity\\FlightEvent' => __DIR__ . '/../..' . '/back/entity/FlightEvent.php',
        'Entity\\FlightSettlement' => __DIR__ . '/../..' . '/back/entity/FlightSettlement.php',
        'Entity\\FlightToFolder' => __DIR__ . '/../..' . '/back/entity/FlightToFolder.php',
        'Entity\\Folder' => __DIR__ . '/../..' . '/back/entity/Folder.php',
        'Entity\\SearchFlightsQuery' => __DIR__ . '/../..' . '/back/entity/SearchFlightsQuery.php',
        'Entity\\User' => __DIR__ . '/../..' . '/back/entity/User.php',
        'Entity\\UserActivity' => __DIR__ . '/../..' . '/back/entity/UserActivity.php',
        'Entity\\UserAuth' => __DIR__ . '/../..' . '/back/entity/UserAuth.php',
        'Entity\\UserSetting' => __DIR__ . '/../..' . '/back/entity/UserSetting.php',
        'Model\\Airport' => __DIR__ . '/../..' . '/back/model/Airport.php',
        'Model\\Calibration' => __DIR__ . '/../..' . '/back/model/Calibration.php',
        'Model\\Channel' => __DIR__ . '/../..' . '/back/model/Channel.php',
        'Model\\DataBaseConnector' => __DIR__ . '/../..' . '/back/model/DataBaseConnector.php',
        'Model\\Fdr' => __DIR__ . '/../..' . '/back/model/Fdr.php',
        'Model\\Flight' => __DIR__ . '/../..' . '/back/model/Flight.php',
        'Model\\FlightComments' => __DIR__ . '/../..' . '/back/model/FlightComments.php',
        'Model\\FlightException' => __DIR__ . '/../..' . '/back/model/FlightException.php',
        'Model\\Folder' => __DIR__ . '/../..' . '/back/model/Folder.php',
        'Model\\Frame' => __DIR__ . '/../..' . '/back/model/Frame.php',
        'Model\\Language' => __DIR__ . '/../..' . '/back/model/Language.php',
        'Model\\PSTempl' => __DIR__ . '/../..' . '/back/model/ParamSetTemplate.php',
        'Model\\SearchFlights' => __DIR__ . '/../..' . '/back/model/SearchFlights.php',
        'Model\\User' => __DIR__ . '/../..' . '/back/model/User.php',
        'Model\\UserOptions' => __DIR__ . '/../..' . '/back/model/UserOptions.php',
        'Repository\\FlightEventRepository' => __DIR__ . '/../..' . '/back/repository/FlightEventRepository.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit957bae1b0e3e2bc3fe568f00ac4778eb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit957bae1b0e3e2bc3fe568f00ac4778eb::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit957bae1b0e3e2bc3fe568f00ac4778eb::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit957bae1b0e3e2bc3fe568f00ac4778eb::$classMap;

        }, null, ClassLoader::class);
    }
}
