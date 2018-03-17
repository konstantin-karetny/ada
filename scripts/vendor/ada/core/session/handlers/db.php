<?php
    /**
    * @package   ada/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Handlers;

    class Db extends \Ada\Core\Session\Handler {

        public function close(): bool {
            exit(var_dump( __METHOD__ ));
        }

        public function create_sid() {
            exit(var_dump( __METHOD__ ));
        }

        public function destroy($session_id): bool {
            exit(var_dump( __METHOD__ ));
        }

        public function gc($maxlifetime): bool {
            exit(var_dump( __METHOD__ ));
        }

        public function open($save_path, $session_name): bool {
            exit(var_dump( __METHOD__ ));
        }

        public function read($session_id): string {
            exit(var_dump( __METHOD__ ));
        }

        public function write($session_id, $session_data): bool {
            exit(var_dump( __METHOD__ ));
        }

    }
