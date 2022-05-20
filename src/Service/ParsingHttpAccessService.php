<?php

namespace HttpAccessParser\Service;

use HttpAccessParser\Repository\RepositoryInterface;

/**
 * Сервис, предназначенный для парсинга файла
 */
class ParsingHttpAccessService
{
    /**
     * Репозиторий для работы с хранилещем данных
     *
     * @var RepositoryInterface
     */
    private RepositoryInterface $fileRepository;

    /**
     * Количество просмотров
     *
     * @var int
     */
    private int $views = 0;

    /**
     * Данные об URLS для подсчета уникальных
     *
     * @var array
     */
    private array $urls = [];

    /**
     * Объем трафика
     *
     * @var int
     */
    private int $traffic = 0;

    /**
     * Данные статус кодов для их подсчета
     *
     * @var array
     */
    private array $statusCode = [];

    /**
     * Данные о поисковых ботах для их подсчета
     *
     * @var array
     */
    private array $crawlers = [
        'Google'  => 0,
        'Yandex'  => 0,
        'Bing'    => 0,
        'Yahoo'   => 0,
        'Mail.Ru' => 0,
    ];

    /**
     * Названия поисковых роботов
     */
    private const CRAWLERS_NAME = [
        'Google'  => 'Googlebot',
        'Yandex'  => 'YandexBot',
        'Bing'    => 'Bingbot',
        'Yahoo'   => 'Slurp',
        'Mail.Ru' => 'Mail.Ru'
    ];

    /**
     * @param RepositoryInterface $fileRepository - репозиторий для работы с хранилещем
     */
    public function __construct(RepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }


    /**
     * Запуск процесса парсинга
     *
     * @param string $pathToLogFile - путь до файла с логом
     * @param string $pathToDir - путь до папки, в которую нужно сохранить результат
     *
     * @return string
     */
    public function run(string $pathToLogFile, string $pathToDir): string
    {
        $data = [];

        foreach ($this->fileRepository->loadData($pathToLogFile) as $row) {
            $data = $this->parsingData($row);
        }

        return $this->fileRepository->save($data, $pathToDir);
    }

    /**
     * Парсинг данных
     *
     * @param string $row - строка из лог файла
     *
     * @return array
     */
    private function parsingData(string $row): array
    {
        $pattern = '/(?<ip>[\d.]+)\s-\s-\s\[(?<time>[^\]]+)\]\s"(?<type>[^"]+)\s(?<resource>\/[^"]+)' .
        '\s(?<version>[^"]+)"\s(?<status>[\d]+)\s(?<traffic>[\d]+)\s"(?<url>[^"]+)"\s"(?<userAgent>[^"]+)"/';

        preg_match($pattern, $row, $matches);

        $this->buildViewsData();

        $this->buildUrlsData($matches['url'], $matches['resource']);

        $this->buildTrafficData($matches['traffic']);

        $this->buildStatusCodeData($matches['status']);

        $this->buildCrawlersData($matches['userAgent']);

        return [
            'views'      => $this->views,
            'urls'       => count($this->urls),
            'traffic'    => $this->traffic,
            'crawlers'   => $this->crawlers,
            'statusCode' => $this->statusCode
        ];
    }

    /**
     * Подготовка данных о просмотрах
     *
     * @return void
     */
    private function buildViewsData(): void
    {
        $this->views++;
    }

    /**
     * Подготовка данных об уникальных URL
     *
     * @param string $host - хост URL
     * @param string $resource - путь до ресура URL
     *
     * @return void
     */
    private function buildUrlsData(string $host, string $resource): void
    {
        $url = $host . $resource;

        if (false === in_array($url, $this->urls, true)) {
            $this->urls[] = $url;
        }

    }

    /**
     * Подготовка данных об объёме трафика
     *
     * @param int $traffic - объем трафика
     *
     * @return void
     */
    private function buildTrafficData(int $traffic): void
    {
        $this->traffic += $traffic;
    }

    /**
     * Подготовка данных о статусе ответа
     *
     * @param int $status - код http ответа
     *
     * @return void
     */
    private function buildStatusCodeData(int $status): void
    {
        if (false === array_key_exists($status, $this->statusCode)) {
            $this->statusCode[$status] = 1;
        } else {
            $this->statusCode[$status]++;
        }

    }

    /**
     * Подготовка данных о поисковых ботах
     *
     * @param string $userAgent - заголовок user-agent из лога
     *
     * @return void
     */
    private function buildCrawlersData(string $userAgent): void
    {
        foreach (self::CRAWLERS_NAME as $company => $botName) {
            if (false !== strpos($userAgent, $botName)) {
                $this->crawlers[$company]++;
            }
        }
    }
}