<?php

declare(strict_types=1);

namespace PhpMyAdmin\Server\Status;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Html\Generator;
use PhpMyAdmin\Util;

use function __;
use function array_keys;
use function count;
use function mb_strtolower;
use function strlen;
use function ucfirst;

final class Processes
{
    /** @var DatabaseInterface */
    private $dbi;

    public function __construct(DatabaseInterface $dbi)
    {
        $this->dbi = $dbi;
    }

    /**
     * @param array $params Request parameters
     *
     * @return array<string, array|string>
     */
    public function getList(array $params): array
    {
        $urlParams = [];

        $showFullSql = ! empty($params['full']);
        if ($showFullSql) {
            $urlParams['full'] = '';
        } else {
            $urlParams['full'] = 1;
        }

        $sqlQuery = $showFullSql
            ? 'SHOW FULL PROCESSLIST'
            : 'SHOW PROCESSLIST';
        if (
            (! empty($params['order_by_field'])
                && ! empty($params['sort_order']))
            || ! empty($params['showExecuting'])
        ) {
            $urlParams['order_by_field'] = $params['order_by_field'];
            $urlParams['sort_order'] = $params['sort_order'];
            $urlParams['showExecuting'] = $params['showExecuting'];
            $sqlQuery = 'SELECT * FROM `INFORMATION_SCHEMA`.`PROCESSLIST` ';
        }

        if (! empty($params['showExecuting'])) {
            $sqlQuery .= ' WHERE state != "" ';
        }

        if (! empty($params['order_by_field']) && ! empty($params['sort_order'])) {
            $sqlQuery .= ' ORDER BY '
                . Util::backquote($params['order_by_field'])
                . ' ' . $params['sort_order'];
        }

        $result = $this->dbi->query($sqlQuery);
        $rows = [];
        while ($process = $this->dbi->fetchAssoc($result)) {
            // Array keys need to modify due to the way it has used
            // to display column values
            if (
                (! empty($params['order_by_field']) && ! empty($params['sort_order']))
                || ! empty($params['showExecuting'])
            ) {
                foreach (array_keys($process) as $key) {
                    $newKey = ucfirst(mb_strtolower($key));
                    if ($newKey === $key) {
                        continue;
                    }

                    $process[$newKey] = $process[$key];
                    unset($process[$key]);
                }
            }

            $rows[] = [
                'id' => $process['Id'],
                'user' => $process['User'],
                'host' => $process['Host'],
                'db' => ! isset($process['db']) || strlen($process['db']) === 0 ? '' : $process['db'],
                'command' => $process['Command'],
                'time' => $process['Time'],
                'state' => ! empty($process['State']) ? $process['State'] : '---',
                'progress' => ! empty($process['Progress']) ? $process['Progress'] : '---',
                'info' => ! empty($process['Info']) ? Generator::formatSql(
                    $process['Info'],
                    ! $showFullSql
                ) : '---',
            ];
        }

        return [
            'columns' => $this->getSortableColumnsForProcessList($showFullSql, $params),
            'rows' => $rows,
            'refresh_params' => $urlParams,
        ];
    }

    private function getSortableColumnsForProcessList(bool $showFullSql, array $params): array
    {
        // This array contains display name and real column name of each
        // sortable column in the table
        $sortableColumns = [
            [
                'column_name' => __('ID'),
                'order_by_field' => 'Id',
            ],
            [
                'column_name' => __('User'),
                'order_by_field' => 'User',
            ],
            [
                'column_name' => __('Host'),
                'order_by_field' => 'Host',
            ],
            [
                'column_name' => __('Database'),
                'order_by_field' => 'db',
            ],
            [
                'column_name' => __('Command'),
                'order_by_field' => 'Command',
            ],
            [
                'column_name' => __('Time'),
                'order_by_field' => 'Time',
            ],
            [
                'column_name' => __('Status'),
                'order_by_field' => 'State',
            ],
            [
                'column_name' => __('Progress'),
                'order_by_field' => 'Progress',
            ],
            [
                'column_name' => __('SQL query'),
                'order_by_field' => 'Info',
            ],
        ];

        $sortableColCount = count($sortableColumns);

        $columns = [];
        foreach ($sortableColumns as $columnKey => $column) {
            $is_sorted = ! empty($params['order_by_field'])
                && ! empty($params['sort_order'])
                && ($params['order_by_field'] == $column['order_by_field']);

            $column['sort_order'] = 'ASC';
            if ($is_sorted && $params['sort_order'] === 'ASC') {
                $column['sort_order'] = 'DESC';
            }

            if (isset($params['showExecuting'])) {
                $column['showExecuting'] = 'on';
            }

            $columns[$columnKey] = [
                'name' => $column['column_name'],
                'params' => $column,
                'is_sorted' => $is_sorted,
                'sort_order' => $column['sort_order'],
                'has_full_query' => false,
                'is_full' => false,
            ];

            if (0 !== --$sortableColCount) {
                continue;
            }

            $columns[$columnKey]['has_full_query'] = true;
            if (! $showFullSql) {
                continue;
            }

            $columns[$columnKey]['is_full'] = true;
        }

        return $columns;
    }
}
