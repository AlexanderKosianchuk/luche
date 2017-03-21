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
        'Component\\EntityManagerComponent' => __DIR__ . '/../..' . '/component/EntityManagerComponent.php',
        'Component\\EventProcessingComponent' => __DIR__ . '/../..' . '/component/EventProcessingComponent.php',
        'Component\\FdrCycloComponent' => __DIR__ . '/../..' . '/component/FdrCycloComponent.php',
        'Component\\FlightComponent' => __DIR__ . '/../..' . '/component/FlightComponent.php',
        'Component\\OSdetectionComponent' => __DIR__ . '/../..' . '/component/OSdetectionComponent.php',
        'Component\\RealConnectionFactory' => __DIR__ . '/../..' . '/component/RealConnectionComponent.php',
        'Controller\\CController' => __DIR__ . '/../..' . '/controller/CController.php',
        'Controller\\CalibrationController' => __DIR__ . '/../..' . '/controller/CalibrationController.php',
        'Controller\\ChartController' => __DIR__ . '/../..' . '/controller/ChartController.php',
        'Controller\\EntryController' => __DIR__ . '/../..' . '/controller/EntryController.php',
        'Controller\\FdrController' => __DIR__ . '/../..' . '/controller/FdrController.php',
        'Controller\\FlightsController' => __DIR__ . '/../..' . '/controller/FlightsController.php',
        'Controller\\IndexController' => __DIR__ . '/../..' . '/controller/IndexController.php',
        'Controller\\PrinterController' => __DIR__ . '/../..' . '/controller/PrinterController.php',
        'Controller\\SearchFlightController' => __DIR__ . '/../..' . '/controller/SearchFlightController.php',
        'Controller\\UploaderController' => __DIR__ . '/../..' . '/controller/UploaderController.php',
        'Controller\\UserController' => __DIR__ . '/../..' . '/controller/UserController.php',
        'Controller\\ViewOptionsController' => __DIR__ . '/../..' . '/controller/ViewOptionsController.php',
        'Entity\\Airport' => __DIR__ . '/../..' . '/entity/Airport.php',
        'Entity\\Calibration' => __DIR__ . '/../..' . '/entity/Calibration.php',
        'Entity\\Event' => __DIR__ . '/../..' . '/entity/Event.php',
        'Entity\\EventSettlement' => __DIR__ . '/../..' . '/entity/EventSettlement.php',
        'Entity\\EventToFdr' => __DIR__ . '/../..' . '/entity/EventToFdr.php',
        'Entity\\Fdr' => __DIR__ . '/../..' . '/entity/Fdr.php',
        'Entity\\FdrToUser' => __DIR__ . '/../..' . '/entity/FdrToUser.php',
        'Entity\\Flight' => __DIR__ . '/../..' . '/entity/Flight.php',
        'Entity\\FlightComments' => __DIR__ . '/../..' . '/entity/FlightComment.php',
        'Entity\\FlightEvent' => __DIR__ . '/../..' . '/entity/FlightEvent.php',
        'Entity\\FlightSettlement' => __DIR__ . '/../..' . '/entity/FlightSettlement.php',
        'Entity\\FlightToFolder' => __DIR__ . '/../..' . '/entity/FlightToFolder.php',
        'Entity\\Folder' => __DIR__ . '/../..' . '/entity/Folder.php',
        'Entity\\SearchFlightsQuery' => __DIR__ . '/../..' . '/entity/SearchFlightsQuery.php',
        'Entity\\User' => __DIR__ . '/../..' . '/entity/User.php',
        'Entity\\UserActivity' => __DIR__ . '/../..' . '/entity/UserActivity.php',
        'Entity\\UserAuth' => __DIR__ . '/../..' . '/entity/UserAuth.php',
        'Entity\\UserSetting' => __DIR__ . '/../..' . '/entity/UserSetting.php',
        'Model\\Airport' => __DIR__ . '/../..' . '/model/Airport.php',
        'Model\\Calibration' => __DIR__ . '/../..' . '/model/Calibration.php',
        'Model\\Channel' => __DIR__ . '/../..' . '/model/Channel.php',
        'Model\\DataBaseConnector' => __DIR__ . '/../..' . '/model/DataBaseConnector.php',
        'Model\\Fdr' => __DIR__ . '/../..' . '/model/Fdr.php',
        'Model\\Flight' => __DIR__ . '/../..' . '/model/Flight.php',
        'Model\\FlightComments' => __DIR__ . '/../..' . '/model/FlightComments.php',
        'Model\\FlightException' => __DIR__ . '/../..' . '/model/FlightException.php',
        'Model\\Folder' => __DIR__ . '/../..' . '/model/Folder.php',
        'Model\\Frame' => __DIR__ . '/../..' . '/model/Frame.php',
        'Model\\Language' => __DIR__ . '/../..' . '/model/Language.php',
        'Model\\PSTempl' => __DIR__ . '/../..' . '/model/ParamSetTemplate.php',
        'Model\\SearchFlights' => __DIR__ . '/../..' . '/model/SearchFlights.php',
        'Model\\User' => __DIR__ . '/../..' . '/model/User.php',
        'Model\\UserOptions' => __DIR__ . '/../..' . '/model/UserOptions.php',
        'Repository\\FlightEventRepository' => __DIR__ . '/../..' . '/repository/FlightEventRepository.php',
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
