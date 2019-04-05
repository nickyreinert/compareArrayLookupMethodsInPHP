<?php
/*
	available lookup methods:
		in_array_strict
		in_array_non_strict
		foreach_strict
		foreach_non_strict
		isset
		intersect
		array_keys
		array_search_strict
		array_search_non_strict

*/

	while (@ob_end_flush());

	ini_set('memory_limit', '16024M');
	ini_set('set_time_limit', 9999);
	ini_set('set_time_limit', -1);
	ini_set('max_execution_time', 9999);
	ini_set('max_execution_time', -1);
	ini_set('display_errors',  FALSE);
	ini_set('error_reporting', FALSE);

	class CompareLookups {

		private $lookupMethod = NULL;

		private $haystack = NULL;

		private $needle = NULL;

		private $initArrayLength = 10;

		private $arrayValueLengths = NULL;

		private $breakAfterFound = FALSE;

		private $forceNewRandomArray = FALSE;

		private $maxIterations = 5;

		private $maxPowers = 3;

		private $currentResults = array();

		private $disableOptimization = FALSE;

		public function __construct($parameterList) {

			// assign parameters
			//

			$this->initArrayLength = $parameterList['initArrayLength'];

			$this->arrayValueLengths = explode(',', $parameterList['arrayValueLengths']);

			$this->lookupMethod = $parameterList['lookupMethod'];

			$this->maxPowers = $parameterList['maxPowers'];

			$this->maxIterations = $parameterList['maxIterations'];

			$this->breakAfterFound = $parameterList['breakAfterFound'];

			$this->forceNewRandomArray = $parameterList['forceNewRandomArray'];

			$this->disableOptimization = $parameterList['disableOptimization'];

			// display csv-like header
			//

			$this->displayResultHeader();


			// put global parameters, they will not change for this call
			//

			$this->currentResults['date'] = date('Y-m-d H:i:s');

			$this->currentResults['php_version'] = $parameterList['phpVersion'];

			$this->currentResults['comment'] = $parameterList['comment'];

			$this->currentResults['break_after_found'] = ($this->breakAfterFound) ? 'true' : 'false';

			$this->currentResults['force_new_array'] = ($this->forceNewRandomArray) ? 'true' : 'false';

			$this->currentResults['optimiziation_disabled'] = ($this->disableOptimization) ? 'true' : 'false';

			// first random array
			//

			foreach ($this->arrayValueLengths as $currentArrayValueLength) {

				$this->currentResults['array_value_size'] = $currentArrayValueLength;

				for ($power = 1; $power <= $this->maxPowers; $power++) {

					$currentArrayLength = pow($this->initArrayLength, $power);

					$this->currentResults['array_size'] = $currentArrayLength;

					for ($iteration = 1; $iteration <= $this->maxIterations; $iteration++) {

						$this->currentResults['lookup_method'] = $this->lookupMethod;

						if ($this->forceNewRandomArray == TRUE ||
							!is_array($this->haystack))

							{ $this->createRandomArray($currentArrayLength, $currentArrayValueLength); }


						switch ($this->lookupMethod) {

							case 'in_array_strict':

								$this->useInArray(TRUE);
								break;

							case 'in_array_non_strict':

								$this->useInArray(FALSE);
								break;

							case 'foreach_strict':

								$this->useForEachStrict();
								break;

							case 'foreach_non_strict':

								$this->useForEachNonStrict();
								break;

							case 'isset':

								$this->useIsset();
								break;

							case 'intersect':

								$this->useIntersect();
								break;

								case 'array_search_strict':

									$this->useArraySearch(TRUE);
									break;

								case 'array_search_non_strict':

									$this->useArraySearch(FALSE);
									break;

								case 'array_keys_strict':

									$this->useArrayKeys(TRUE);
									break;

								case 'array_keys_non_strict':

									$this->useArrayKeys(FALSE);
									break;


							default:

								echo 'Unsupported lookup method given: '.$this->lookupMethod;
								die();

						}

						$this->currentResults['iteration'] = $iteration;

						$this->displayCurrentResults();

					}

				}

			}

			if (php_sapi_name() !== 'cgi-fcgi') {

				echo '</table>';

			}

		}

		private function displayCurrentResults() {

			if (php_sapi_name() === 'cgi-fcgi') {

				foreach ($this->resultHeader as $index => $column) {

					echo $this->currentResults[$column];

					if ($index < sizeof($this->resultHeader) - 1) {echo ';';}

				}

				echo PHP_EOL;

			} else {

				echo '</tr>';

				foreach ($this->resultHeader as $index => $column) {

					echo '<td>'. $this->currentResults[$column] .'</td>';

				}

				echo '</tr>';

			}

		}

		private function displayResultHeader() {

			$this->resultHeader = array(

				'iteration',
				'array_size',
				'array_value_size',
				'break_after_found',
				'force_new_array',
				'optimiziation_disabled',
				'lookup_method',
				'delay',
				'memory_usage',
				'php_version',
				'comment',
				'date'

			);

			if (php_sapi_name() === 'cgi-fcgi') {

				echo implode(';', $this->resultHeader) . PHP_EOL;

			} else {

				echo '<table><tr>';

				foreach ($this->resultHeader as $column) {

					echo '<th>'.$column.'</th>';

				}
				echo '<tr>';

			}

		}

		private function createRandomArray($arrayLength, $arrayValueLength) {

			$seed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

			$this->haystack = array();

			$this->needle = NULL;

			for ($i = 0; $i < $arrayValueLength; $i++) {

				$this->needle .= '_';

			}

			for ($i = 1; $i < $arrayLength; $i++) {

				$randomString = '';

				for ($j = 0; $j < $arrayValueLength; $j++) {

					$randomString .= $seed[rand() % strlen($seed)];

				}

				$this->haystack[] = $randomString;

			}

			$this->haystack[] = $this->needle;

		}

		private function useInArray($strictMode) {

			$startTime = microtime(true);

			if ($this->disableOptimization) {sleep(0);}

			$result = in_array($this->needle, $this->haystack, $strictMode);

			$endTime = microtime(true);

			$this->currentResults['delay'] = number_format(($endTime - $startTime), 25, ",", ".");

			$this->currentResults['memory_usage'] = memory_get_usage(true);

		}

		private function useArraySearch($strictMode) {

			$startTime = microtime(true);

			if ($this->disableOptimization) {sleep(0);}

			$result = array_search($this->needle, $this->haystack, $strictMode);

			$endTime = microtime(true);

			$this->currentResults['delay'] = number_format(($endTime - $startTime), 25, ",", ".");

			$this->currentResults['memory_usage'] = memory_get_usage(true);

		}

		private function useIsset() {

			$startTime = microtime(true);

			if ($this->disableOptimization) {sleep(0);}

			$flippedArray = array_flip($this->haystack);

			$result = isset($flippedArray[$this->needle]);

			$endTime = microtime(true);

			$this->currentResults['delay'] = number_format(($endTime - $startTime), 25, ",", ".");

			$this->currentResults['memory_usage'] = memory_get_usage(true);

		}

		private function useIntersect() {

			$startTime = microtime(true);

			if ($this->disableOptimization) {sleep(0);}

			if (count(array_intersect(array($this->needle), $this->haystack))>0) {

				$found = TRUE;

			}

			$endTime = microtime(true);

			$this->currentResults['delay'] = number_format(($endTime - $startTime), 25, ",", ".");

			$this->currentResults['memory_usage'] = memory_get_usage(true);

		}

		private function useArrayKeys($strictMode) {

			$startTime = microtime(true);

			if ($this->disableOptimization) {sleep(0);}

			if (count(array_keys($this->haystack, $this->needle, $strictMode)) > 0) {

				$found = TRUE;

			}

			$endTime = microtime(true);

			$this->currentResults['delay'] = number_format(($endTime - $startTime), 25, ",", ".");

			$this->currentResults['memory_usage'] = memory_get_usage(true);

		}

		private function useForEachStrict() {

			$startTime = microtime(true);

			if ($this->disableOptimization) {sleep(0);}

			foreach ($this->haystack as $key) {

				if ($key === $this->needle) {

					$found = TRUE;

					if ($this->breakAfterFound == TRUE && $found == TRUE) {break;}

				}

			}

			$endTime = microtime(true);

			$this->currentResults['delay'] = number_format(($endTime - $startTime), 25, ",", ".");

			$this->currentResults['memory_usage'] = memory_get_usage(true);

		}

		private function useForEachNonStrict() {

			$startTime = microtime(true);

			if ($this->disableOptimization) {sleep(0);}

			foreach ($this->haystack as $key) {

				if ($key == $this->needle) {

					$found = TRUE;

					if ($this->breakAfterFound == TRUE && $found == TRUE) {break;}

				}

			}

			$endTime = microtime(true);

			$this->currentResults['delay'] = number_format(($endTime - $startTime), 25, ",", ".");

			$this->currentResults['memory_usage'] = memory_get_usage(true);

		}


	}



	$parameterList = array(

		// what lookup method you wanna measure, mandatory parameter!
		'lookupMethod'			=> 'not_set',

		// length of random array for first loop
		'initArrayLength'		=> 10,

		// from init length powers to this max value, e.g. if set to 4: 10^1, 10^2, 10^3, 10^4
		'maxPowers'				=> 5,

		// lenght of values that'll fill the array, comma separated list of strings, will be converted to array!
		'arrayValueLengths'		=> "1,5",

		// how many loops for each array length x array key length
		'maxIterations'			=> 100,

		// allow foreach loop to break the loop after value is found
		'breakAfterFound'		=> TRUE,

		// randomize array every time to prevent any caching features
		'forceNewRandomArray'	=> TRUE,

		// if set to TRUE, a sleep(0) will be added after every lookup function, this will prevent php optimiziation which sometimes leads to a duration of 0 seconds
		'disableOptimization'	=> FALSE,

		// just additional details for the output and analysis
		'phpVersion' 			=> NULL,

		'comment' 			=> NULL

	);

	foreach ($parameterList as $parameterName => &$value) {

		$value = (isset($_GET[$parameterName])) ? $_GET[$parameterName] : $value;

		// if (php_sapi_name() !== 'cgi-fcgi') {

			// echo $parameterName . ': '.json_encode($value) . '<br />';

		// } else {

			// echo $parameterName . ': '.json_encode($value) . PHP_EOL;

		// }

	}


	$compareLookups = new CompareLookups($parameterList);
