<?php
namespace trntv\filekit\base;
/**
 * Eugine Terentev <eugine@terentev.net>
 */

interface FileRecordInterface {
    public function saveRecord();
    public function findByBasename($basename, $repository = null);
}