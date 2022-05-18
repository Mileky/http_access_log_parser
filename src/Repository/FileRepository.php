<?php

namespace HttpAccessParser\Repository;

use Generator;
use JsonException;

/**
 * Класс, отвечающий за работу с хранилещем данных (файлом)
 */
class FileRepository implements RepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function loadData(string $pathToLogFile): Generator
    {
        $file = fopen(__DIR__ . "/../../$pathToLogFile", 'rb');

        while (!feof($file)) {
            $row = fgets($file);

            if ($row === false) {
                break;
            }

            yield $row;
        }

        fclose($file);
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, string $pathToDir): string
    {
        $jsonData = $this->serializeToJson($data);

        file_put_contents(__DIR__ . "/../../$pathToDir/result.json", $jsonData);

        return $jsonData;
    }

    /**
     * Представление данных в формат json
     *
     * @param array $data
     *
     * @return string
     * @throws JsonException
     */
    private function serializeToJson(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

}