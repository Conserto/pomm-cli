#!/usr/bin/env php
<?php
/*
 * This file is part of the Pomm's Cli package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Application;

use PommProject\Cli\Command\InspectSchema;
use PommProject\Cli\Command\InspectRelation;
use PommProject\Cli\Command\InspectDatabase;
use PommProject\Cli\Command\InspectConfig;
use PommProject\Cli\Command\GenerateRelationStructure;
use PommProject\Cli\Command\GenerateRelationModel;
use PommProject\Cli\Command\GenerateEntity;
use PommProject\Cli\Command\GenerateForRelation;
use PommProject\Cli\Command\GenerateForSchema;

function includeIfExists(string $file): mixed
{
    if (file_exists($file)) {
        return include $file;
    }
    return null;
}

if (
    (!$loader = includeIfExists(__DIR__ . '/../vendor/autoload.php'))
    && (!$loader = includeIfExists(__DIR__ . '/../../../autoload.php'))
) {
    die(
        'You must set up the project dependencies, run the following commands:' . PHP_EOL
        . 'curl -s http://getcomposer.org/installer | php' . PHP_EOL
        . 'php composer.phar install' . PHP_EOL
    );
}

$application = new Application('pomm', 'NextGen early-dev');
$application->addCommand(new InspectConfig);
$application->addCommand(new InspectDatabase);
$application->addCommand(new InspectSchema);
$application->addCommand(new InspectRelation);
$application->addCommand(new GenerateRelationStructure);
$application->addCommand(new GenerateRelationModel);
$application->addCommand(new GenerateEntity);
$application->addCommand(new GenerateForRelation);
$application->addCommand(new GenerateForSchema);
$application->run();
