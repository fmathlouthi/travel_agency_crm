<?php
/**
 * Created by Hugues
 */

namespace App\Service;


use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Stores 4 statistics
 */
class YamlStatsProvider
{

    public function setStats(array $stats) {

        $yaml = Yaml::dump($stats,2);

        try {
            file_put_contents(__DIR__ . '/stats.yaml', $yaml);
        } catch (DumpException $exception) {
            printf('Unable to write stats to file: %s', $exception->getMessage());
        }

    }

    public function getStats()
    {
        try {
            return Yaml::parseFile(__DIR__ . '/stats.yaml');
        } catch (ParseException $exception) {
            printf('Unable to parse YAML file: %s', $exception->getMessage());
        }
    }

}