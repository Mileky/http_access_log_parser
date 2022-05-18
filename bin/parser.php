<?php

use HttpAccessParser\Command\ParserHttpAccessLogCommand;
use HttpAccessParser\Repository\FileRepository;
use HttpAccessParser\Service\ParsingHttpAccessService;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$application = new Application();
$application->add(
    new ParserHttpAccessLogCommand(
        new ParsingHttpAccessService(
            new FileRepository()
        )
    )
);

$application->run();