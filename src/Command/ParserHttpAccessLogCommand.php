<?php

namespace HttpAccessParser\Command;

use HttpAccessParser\Exception\InvalidDataException;
use HttpAccessParser\Service\ParsingHttpAccessService;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ParserHttpAccessLogCommand extends Command
{
    /**
     * Сервис для парсинга http_access.log
     *
     * @var ParsingHttpAccessService
     */
    private ParsingHttpAccessService $parsingService;

    /**
     * @param ParsingHttpAccessService $parsingService - Сервис для парсинга http_access.log
     */
    public function __construct(ParsingHttpAccessService $parsingService)
    {
        $this->parsingService = $parsingService;

        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('accessLog:parse');
        $this->setDescription('Парсинг http access лога');

        $this->addArgument(
            'logPath',
            InputArgument::REQUIRED,
            'Путь до файла с логом'
        );
        $this->addArgument(
            'dirPathOutput',
            InputArgument::REQUIRED,
            'Путь до папки, в которую нужно сохранить результат'
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pathToLogFile = $input->getArgument('logPath');
        $pathToDir = $input->getArgument('dirPathOutput');

        $this->validateParams($pathToLogFile, $pathToDir);

        try {
            $data = $this->parsingService->run($pathToLogFile, $pathToDir);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }

        $output->write($data);

        return self::SUCCESS;
    }

    /**
     * Валидация данных о файле с логами и папке для сохранения результата
     *
     * @param $pathToLogFile - путь до файла с логами
     * @param $pathToDir - папка, в которую нужно сохранить результат парсинга
     *
     * @return void
     */
    private function validateParams($pathToLogFile, $pathToDir): void
    {
        if (false === file_exists($pathToLogFile)) {
            throw new InvalidDataException('Не удалось найти файл с логами');
        }
        if (false === is_dir($pathToDir)) {
            throw new InvalidDataException('Не удалось найти папку для сохранения результатов');
        }
    }


}