<?php

    namespace IDM;
    interface IDMInsterface {
        /**
         * @param string $uuid
         * @return
         */
        public function isExisted(string $uuid);

        /**
         * @param string $uid
         * @param string $authSource
         * @return
         */
        public function isExisted2(string $uid, string $authSource);

        /**
         * @param string $uuid
         * @return
         */
        public function getStandardClaims(string $uuid);
    }

