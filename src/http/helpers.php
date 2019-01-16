<?php

if (!function_exists('refiner')) {
    /**
     * @param $request
     * @param bool $inputsHasObsceneWords
     *
     * @return \Illuminate\Support\Collection
     */
    function refiner($request, $inputsHasObsceneWords = false)
    {
        try {
            // Get all requested inputs
            $requestData = $request->all();
            $containObsceneWords = false;

            if (config('refiner')) { // Check refiner config file is published or not

                // Get all obscene words from the config file
                $detectWords = config('refiner');
                $detectWords = array_flip($detectWords);

                if ($requestData) { // Check request has inputs or not
                    foreach ($requestData as $individualKey => $individualValue) { // Individually check all input string
                        $filterString = [];
                        $requestedString = [];

                        if (!is_array($individualValue)) { // Convert input value into an array by space
                            $requestedString = strtolower($individualValue);
                            $requestedString = explode(' ', $requestedString);
                        }

                        foreach ($requestedString as $words) { // Check individual input's words, is they contain obscene word or not
                            if ($inputsHasObsceneWords == false) { // Set words into new variable except for obscene words if a request for inputs removing obscene words
                                if (!isset($detectWords[$words])) {
                                    $filterString[] = $words;
                                }
                            }

                            if ($inputsHasObsceneWords == true) { // Set return flag if a request to check inputs has obscene words
                                if (isset($detectWords[$words])) {
                                    $containObsceneWords = true;
                                }
                            }
                        }

                        if ($inputsHasObsceneWords == false) { // Update input value without obscene words if a request for inputs removing obscene words
                            $filterString = implode(' ', $filterString);
                            $request = $request->merge([$individualKey => $filterString]);
                        }
                    }
                }
            }

            if ($inputsHasObsceneWords == false) { // Set response base on the request type
                return $request;
            } else {
                return $containObsceneWords;
            }
        } catch (\Exception $ex) {
            // Generate a log message if any error caught
            \Log::error($ex->getMessage());

            if ($inputsHasObsceneWords == false) { // Set response base on the request type
                return collect([]);
            } else {
                return false;
            }
        }
    }
}
