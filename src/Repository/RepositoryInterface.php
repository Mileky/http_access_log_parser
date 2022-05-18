<?php

namespace HttpAccessParser\Repository;

use Generator;

/**
 * Интерфейс репозиториев
 */
interface RepositoryInterface
{
    /**
     * Чтение данных из файла
     *
     * @param string $pathToLogFile - путь до файла
     *
     * @return Generator
     */
    public function loadData(string $pathToLogFile): Generator;

    /**
     * Сохранение данных
     *
     * @param array $data - данные для сохранения
     * @param string $pathToDir - путь до папки, в которую нужно сохранить
     *
     * @return string
     */
    public function save(array $data, string $pathToDir): string;
}