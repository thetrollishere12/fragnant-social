<?php




namespace App\Helper;




use Illuminate\Support\Facades\Http;
use Storage;
use App\Models\SystemScriptElement;
use Illuminate\Support\Str;



class OpenAiHelper
{



public static function AiGenerateText($string, $array = []) {


    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => (isset($array['model']) ? $array['model'] : "gpt-4o"),
        'messages' => [
            ["role" => "system", "content" => "You are a helpful assistant."],
            ["role" => "user", "content" => $string]
        ],
        'temperature' => (isset($array['temperature']) ? $array['temperature'] : 1),
        'max_tokens' => (isset($array['max_tokens']) ? $array['max_tokens'] : 4000)
    ]);

    try {
        return json_decode($response->body())->choices[0]->message->content;
    } catch (\Exception $e) {
        dd(json_decode($response->body()));
    }
}



public static function AiGenerateImg($prompt, $size = '1024x1024', $quality = 'standard') {

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
    ])->post('https://api.openai.com/v1/images/generations', [
        'model' => 'dall-e-3',
        'prompt' => $prompt,
        'size' => $size,
        'quality' => $quality,
        'n' => 1,
    ]);

    return json_decode($response->body());
}




public static function string_number_to_number($string) {
    $array = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen'];

    foreach ($array as $key => $value) {
        $string = preg_replace('/\b' . $value . '\b/', $key, $string);
    }

    return $string;
}







 // Split the text into smaller parts at word boundaries
public static function splitTextIntoChunks($text, $maxLength) {
    $chunks = [];
    $currentPosition = 0;

    while ($currentPosition < strlen($text)) {
        $chunk = substr($text, $currentPosition, $maxLength);

        // Ensure we don't split in the middle of a word
        if (strlen($chunk) < $maxLength || $chunk[$maxLength - 1] === ' ') {
            $chunks[] = trim($chunk);
            $currentPosition += strlen($chunk);
        } else {
            $lastSpacePosition = strrpos($chunk, ' ');
            if ($lastSpacePosition === false) {
                // No space found, use the chunk as is
                $chunks[] = trim($chunk);
                $currentPosition += strlen($chunk);
            } else {
                // Split at the last space
                $chunks[] = trim(substr($chunk, 0, $lastSpacePosition));
                $currentPosition += $lastSpacePosition + 1;
            }
        }
    }

    return $chunks;
}





public static function convertMultilineToSingleLine($text)
{
    // Remove all types of line breaks and replace them with a single space
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    return $text;
}



   






public static function TextToSpeech($text, $voice = null, $speed = 1)
{
    // Define a list of possible voices
    $voices = ['alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer'];

    // If no voice is provided, select a random one
    if (is_null($voice)) {
        $voice = $voices[array_rand($voices)];
    }

    // Split the text into smaller parts at word boundaries
    $textParts = OpenAIHelper::splitTextIntoChunks($text, 1500); // Adjust the size as needed

    $audioArray = [];

    foreach ($textParts as $part) {

        sleep(2);

        $maxRetries = 3;
        $attempt = 0;
        $successful = false;

        while ($attempt < $maxRetries && !$successful) {
            try {


                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
                ])->post('https://api.openai.com/v1/audio/speech', [
                    'model' => 'tts-1-hd',
                    'voice' => $voice,
                    'input' => OpenAIHelper::convertMultilineToSingleLine($part),
                    'speed' => $speed
                ]);

                

                if ($response->successful()) {
                    $audioContent = $response->body();

                    // Define path and file name for the mp3 file
                    $filePath = 'speech_files/' . Str::uuid() . '.mp3';

                    // Store the audio file in Laravel's storage
                    Storage::disk('public')->put($filePath, $audioContent);

                    $audioArray[] = Storage::disk('public')->path($filePath);


                    // dd([
                    //     $part,
                    //     OpenAIHelper::convertMultilineToSingleLine($part),
                    //     $filePath,
                    //     $audioArray
                    // ]);



                    $successful = true; // Mark as successful to exit the loop
                } else {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        dd($response->body());
                    } else {
                        sleep(2); // Optional: wait before retrying
                    }
                }
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    dd($e->getMessage());
                } else {
                    sleep(2); // Optional: wait before retrying
                }
            }
        }
    }

    // Check the number of audio files generated
    if (count($audioArray) > 1) {
        // Combine audio files only if there are multiple parts
        $combinedFilePath = 'speech_files/' . Str::uuid() . '_combined.mp3';
        $combinedFileFullPath = Storage::disk('public')->path($combinedFilePath);

        $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

        $fileListPath = Storage::disk('public')->path('openAI/file_list.txt');

        $fileListContent = implode("\n", array_map(fn($filePath) => "file '$filePath'", $audioArray));

        Storage::disk('public')->put('openAI/file_list.txt', $fileListContent);

        $command = "$ffmpegPath -f concat -safe 0 -i \"$fileListPath\" -c copy \"$combinedFileFullPath\"";

        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            return response()->json(['error' => 'Failed to combine audio files', 'details' => $output], 500);
        }

        return response()->json(['message' => 'Speech file generated and combined successfully!', 'path' => Storage::disk('public')->path($combinedFilePath), 'filepath' => $combinedFilePath]);
    } elseif (count($audioArray) == 1) {
        // Return the single audio file if no combination is needed
        return response()->json(['message' => 'Speech file generated successfully!', 'path' => $audioArray[0], 'filepath' => $audioArray[0]]);
    } else {
        // Handle case where no audio files were generated
        dd($audioArray,$text,$textParts);
        return response()->json(['error' => 'No audio files were generated'], 500);
    }
}







    // Function to analyze an image and return a description
    public static function AiAnalyzeImage($imageUrl,$prompt = 'What’s in this image? Keep it very simple') {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'type' => 'text',
                        'text' => $prompt
                    ])
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $imageUrl,
                            'detail' => 'high'
                        ]
                    ])
                ]
            ],
            'max_tokens' => 500
        ]);

        try {
            return json_decode($response->body())->choices[0]->message->content;
        } catch (\Exception $e) {
            dd(json_decode($response->body()));
        }
    }




    // Function to analyze an image and return a description
public static function AnalyzeFrames($imagePath)
{
    try {
        // Get the image content and encode it in base64
        $imageData = Storage::disk('public')->allFiles($imagePath);

        if(count($imageData) > 0){


            $base64Frames = [];

            foreach ($imageData as $key => $data) {
                
                $base64Frames[] = 'data:image/jpeg;base64,' . base64_encode(Storage::disk('public')->get($data));

            }

            

            // Step 2: Create Prompt Messages
            $framesSubset = array_slice($base64Frames, 0, count($base64Frames), 50);
            $framesArray = array_map(function ($frame) {
                return ["image_url" => ["url"=>$frame], "type" => "image_url"];
            }, $framesSubset);



            $promptMessages = [
                [
                    "role" => "user",
                    "content" => array_merge(
                        [[
                            "type"=>"text",
                            "text"=>"These are frames from a section of a video. Keep it detailed in a few lines. If there are any text at all put it under textover. Detect if theres any frame changes and if there is break it up and use the start_time and end_time. Each frame represents 1 second Return the response in JSON format.Do not wrap the json codes in JSON markers.

                            Example JSON structure:
                            [{
                                'start_time': 0:00,
                                'end_time': 0:03,
                                'description': 'Talking to a camera'
                            },{
                                'start_time': 0:04,
                                'end_time': 0:05,
                                'description': 'scene of medicine'
                            }]"
                        ]],
                        $framesArray
                    ),
                ],
            ];

            // Step 3: Prepare OpenAI API Request
            $params = [
                "model" => "gpt-4o",
                "messages" => $promptMessages,
                "max_tokens" => 500,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
                'Content-Type' => 'application/json'
            ])->post('https://api.openai.com/v1/chat/completions', $params);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            } else {
                
                dd($response->body());

            }


        }



    } catch (\Exception $e) {
        dd($e->getMessage());
    }
}





    // Function to analyze an image and return a description
public static function AiAnalyzeFrames($imagePath)
{
    try {
        // Get the image content and encode it in base64
        $imageData = Storage::disk('public')->allFiles($imagePath);

        if(count($imageData) > 0){


            $base64Frames = [];

            foreach ($imageData as $key => $data) {
                
                $base64Frames[] = 'data:image/jpeg;base64,' . base64_encode(Storage::disk('public')->get($data));

            }

            

            // Step 2: Create Prompt Messages
            $framesSubset = array_slice($base64Frames, 0, count($base64Frames), 50);
            $framesArray = array_map(function ($frame) {
                return ["image_url" => ["url"=>$frame], "type" => "image_url"];
            }, $framesSubset);



            $promptMessages = [
                [
                    "role" => "user",
                    "content" => array_merge(
                        [[
                            "type"=>"text",
                            "text"=>"These are frames from a section of a video. This is going to a script so can you explain whats this video. Keep it detailed in a few lines. If there are any text at all put it under textover. Any logos put it under logo. Detect if theres any frame changes and if there is break it up and use the start_time and end_time. Each frame represents 1 second Return the response in JSON format.Do not wrap the json codes in JSON markers.

                            Example JSON structure:
                            [{
                                'start_time': 0:00,
                                'end_time': 0:03,
                                'description': 'Talking to a camera',
                                'textover': 'Hi there my name is Sang'
                            },{
                                'start_time': 0:04,
                                'end_time': 0:06,
                                'description': 'scene of medicine',
                                'textover': 'I like to eat bananas'
                            }]
            
                        ]"
                        ]],
                        $framesArray
                    ),
                ],
            ];

            // Step 3: Prepare OpenAI API Request
            $params = [
                "model" => "gpt-4o",
                "messages" => $promptMessages,
                "max_tokens" => 500,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
                'Content-Type' => 'application/json'
            ])->post('https://api.openai.com/v1/chat/completions', $params);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            } else {
                
                dd($response->body());

            }


        }



    } catch (\Exception $e) {
        dd($e->getMessage());
    }
}




    // Function to process an image frame
    public static function AiProcessFrame($frame) {
        $response = Http::attach(
            'file', file_get_contents($frame), 'frame.jpg'
        )->withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/images/analyses', [
            'model' => 'dalle-v1',
            'file' => curl_file_create($frame),
            'response_format' => 'json'
        ]);

        if ($response->successful()) {
            return json_decode($response->body())->data;
        } else {
            return response()->json(['error' => 'Failed to process frame'], $response->status());
        }
    }








    // Function to convert speech to text
    public static function SpeechToText($audioFilePath) {

        $apiKey = env('OPENAI_KEY');
        
        $response = Http::attach(
            'file', $audioFilePath, 'audio.mp3'
        )->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post('https://api.openai.com/v1/audio/transcriptions', [
            'model' => 'whisper-1',
            'response_format' => 'json'
        ]);

        if ($response->successful()) {
            return json_decode($response->body())->text;
        } else {
            return response()->json(['error' => 'Failed to transcribe audio file'], $response->status());
        }
    }









}