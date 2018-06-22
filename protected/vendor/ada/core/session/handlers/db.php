<?php
    /**
    * @package   project/core
    * @version   1.0.0 22.06.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Session\Handlers;

    class Db extends \Ada\Core\Session\Handler {

        protected
            $table = null;

        public function __construct(\Ada\Core\Db\Table $table) {
            $this->table = $table;
        }

        public function destroy($session_id): bool {
            return $this->getDb()->getQuery()
                ->delete()
                ->from($this->getTable()->getName())
                ->where('id', '=', $session_id)
                ->exec();
        }

        public function gc($maxlifetime): bool {
            $db = $this->getDb();
            return $db->getQuery()
                ->delete()
                    ->from($this->getTable()->getName())
                    ->where(
                        'last_stop_datetime',
                        '<',
                        (
                            \Ada\Core\DateTime::init(
                                \Ada\Core\DateTime::init()->getTimestamp()
                                -
                                $maxlifetime
                            )->format(
                                $db->getDateFormat()
                            )
                        )
                    )
                    ->exec();
        }

        public function getDb(): \Ada\Core\Db\Driver {
            return $this->getTable()->getDb();
        }

        public function getTable(): \Ada\Core\Db\Table {
            return $this->table;
        }

        public function read($session_id): string {
            return $this->getDb()->getQuery()
                ->selectOne('data')
                ->from($this->getTable()->getName())
                ->where('id', '=', $session_id)
                ->fetchCell('string');
        }

        public function write($session_id, $session_data): bool {
            $db  = $this->getDb();
            $row = [
                'id'                 => $session_id,
                'data'               => $session_data,
                'last_stop_datetime' => \Ada\Core\DateTime::init()->format(
                    $db->getDateFormat()
                )
            ];
            if (\Ada\Core\Session::init()->isNew()) {
                return $db->getQuery()
                    ->insert($row)
                    ->into($this->getTable()->getName())
                    ->exec();
            }
            unset($row['id']);
            return $db->getQuery()
                ->update($row)
                ->table($this->getTable()->getName())
                ->where('id', '=', $session_id)
                ->exec();
        }

    }
