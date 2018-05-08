<?php
    /**
    * @package   project/core
    * @version   1.0.0 08.05.2018
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
            $db = $this->table->getDb();
            return $db->deleteRow(
                $this->table->getName(),
                $db->q('id') . ' LIKE ' . $db->e($session_id)
            );
        }

        public function gc($maxlifetime): bool {
            $db = $this->table->getDb();
            return $db->deleteRow(
                $this->table->getName(),
                (
                    $db->q('last_stop_datetime') .
                    ' < ' .
                    $db->e(
                        \Ada\Core\DateTime::init(
                            \Ada\Core\DateTime::init()->getTimestamp() - $maxlifetime
                        )->format(
                            $db->getDateFormat()
                        )
                    )
                )
            );
        }

        public function read($session_id): string {
            $db = $this->table->getDb();
            return $db->fetchCell(
                '
                    SELECT ' . $db->q('data') . '
                    FROM '   . $db->t($this->table->getName()) . '
                    WHERE '  . $db->q('id') . ' LIKE ' . $db->e($session_id) . '
                ',
                'string'
            );
        }

        public function write($session_id, $session_data): bool {
            $db  = $this->table->getDb();
            $row = [
                'id'                 => $session_id,
                'data'               => $session_data,
                'last_stop_datetime' => \Ada\Core\DateTime::init()->format(
                    $db->getDateFormat()
                )
            ];
            if (\Ada\Core\Session::init()->isNew()) {
                return $db->insertRow($this->table->getName(), $row);
            }
            unset($row['id']);
            return $db->updateRow(
                $this->table->getName(),
                $row,
                $db->q('id') . ' LIKE ' . $db->e($session_id)
            );
        }

    }
