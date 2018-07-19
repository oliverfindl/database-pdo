<?php
	/**
	 * database-pdo v1.0.5 (2018-07-19)
	 * Copyright 2018 Oliver Findl
	 * @license MIT
	 */

	namespace OliverFindl;

	class Database {
		private $pdo;
		private $args;

		const INSERT_DEFAULT = 0;
		const INSERT_IGNORE = 1;
		const INSERT_UPDATE = 2;
		const INSERT_REPLACE = 3;

		public function __construct() {
			$this->args = func_get_args();
			$this->pdo = $this->init();
		}

		public function __call(string $method, array $args) { //NOTE: return types are not unified
			return call_user_func_array(array($this->pdo, $method), $args);
		}

		private function init(): \PDO {
			return (new \ReflectionClass("PDO"))->newInstanceArgs($this->args);
		}

		public function ping(): bool {
			try {
				$this->pdo->query("SELECT 1;");
			} catch (\PDOException $e) {
				$this->pdo = $this->init();
			}
	 
			return true;
		}

		public function insert(string $table, array $array, int $mode = self::INSERT_DEFAULT): bool {
			if(empty($table) && $this->error("Table argument cannot be empty.")) return false;
			if(empty($array) && $this->error("Array argument cannot be empty.")) return false;
			if(empty($array) || !in_array($mode, array(self::INSERT_DEFAULT, self::INSERT_IGNORE, self::INSERT_UPDATE, self::INSERT_REPLACE)) && $this->error("Mode argument value is not valid.")) return false;

			if($this->is_assoc($array)) $array = array($array);
			$cols = array_keys(reset($array));
			$keys = $vals = array();

			for($i = 0; $i < count($array); $i++) {
				if(!is_array($array[$i])) $array[$i] = (array) $array[$i];
				if(!$this->is_assoc($array[$i]) && $this->error("Array argument must contain only associative arrays.")) return false;

				$keys_chunk = array();
				foreach($cols as $col) {
					$key = ":{$col}_{$i}";
					$vals[$key] = $array[$i][$col];
					$keys_chunk[] = $key;
				}
				$keys[] = $keys_chunk;
			}

			return $this->pdo->prepare(($mode === self::INSERT_REPLACE ? "REPLACE" : ("INSERT" . ($mode === self::INSERT_IGNORE ? " IGNORE" : ""))) . " INTO {$table} (" . implode(", ", $cols) . ") VALUES " . implode(", ", array_map(function($key) { return "(" . implode(", ", $key) . ")"; }, $keys)) . ($mode === self::INSERT_UPDATE ? (" ON DUPLICATE KEY UPDATE " . implode(", ", array_map(function(string $col): string { return "{$col} = VALUES({$col})"; }, $cols))) : "") . ";")->execute($vals);
		}

		private function error(string $error): bool {
			if(empty($this->pdo) || empty($error)) return false;

			switch($this->pdo->getAttribute(\PDO::ATTR_ERRMODE)) {
				default:
				case \PDO::ERRMODE_SILENT: {
					/* nothing */
					break;
				}
				case \PDO::ERRMODE_WARNING: {
					trigger_error($error, E_USER_WARNING);
					break;
				}
				case \PDO::ERRMODE_EXCEPTION: {
					throw new \Exception($error);
					break;
				}
			}

			return error_log($error);
		}

		private function is_assoc(array $array): bool {
			return !empty($array) && array_keys($array) !== range(0, count($array) - 1);
		}
	}
?>
