<?php




namespace App\Helper;

use Illuminate\Support\Facades\Http;
use Storage;
use App\Models\SystemScriptElement;
use Illuminate\Support\Str;


class OpenAiHelper
{
    public static function AiGenerateText($string, $array = []) {
        sleep(1);

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

        $ffmpegPath = 'C:/xampp/htdocs/storwy/ffmpeg/bin/ffmpeg.exe';

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






    public static function GenerateContent($brief) {

        $elements = SystemScriptElement::all(['label', 'definition']); 

        $resultString = '';

        foreach ($elements as $element) {
            $resultString .= "- {$element->label} ({$element->definition})\n";
        }




        $content = "Write a social media short about " . $brief . " using the following labels(in brackets is the definition) and structure. Use the labels as necessary. Ensure the content is in chronological order. Combine multiple notes into one where applicable. Return the response in JSON format.Do not wrap the json codes in JSON markers. If you do provide links, make sure they are real. For Seo Tags come up with 15. Every voice related or part needs to have some sort of media as sibling. Can't be empty

        Labels:
        ".$resultString."

        Example JSON structure:

        [
            {
                'type': 'Title',
                'title': 'Transform Your Mornings with Me!',
                'description': 'About transforming your morning in a different way',
                'children': []
            },
            {
                'type': 'Intro',
                'description': '',
                'children': [
                    {
                        'type': 'Hook',
                        'order': 1,
                        'description': 'Get watchers attention',
                        'children': [  
                            {
                                'type': 'dialog',
                                'description': 'Ever wonder how a great morning routine can change your day?',
                                'children': []
                            },      
                            {
                                'type': 'Media',
                                'description': 'clip of you saying this.',
                                'children': []
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Body',
                'children': [
                    {
                        'type': 'Section',
                        'title': 'Why it matters',
                        'order': 2,
                        'description': 'Description on why it matters etc',
                        'children': [
                            {
                                'type': 'Voiceover',
                                'description': 'Hi, I\'m [Your Name], and today I\'m going to share how a solid morning routine has transformed my life.',
                                'children': []
                            },
                            {
                                'type': 'Media',
                                'description': 'Use a clip of you waking up and starting your day.',
                                'children': []
                            }
                        ]
                    },
                    {
                        'type': 'Section',
                        'title': 'my routine',
                        'order': 3,
                        'description': 'My Morning Routine',
                        'children': [
                        {
                            'type': 'Section',
                            'title': 'step by step',
                            'order': 3.1,
                            'description': 'My Morning Routine',
                            'children': [
                                {
                                    'type': 'Voiceover',
                                    'description': 'Let’s dive into my morning routine step by step.',
                                    'children': []
                                },
                                {
                                    'type': 'Media',
                                    'description': 'Clip of you making a healthy breakfast.',
                                    'children': [
                                        {
                                            'type': 'Note',
                                            'description': 'Mention the benefits of each step.',
                                            'children': []
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            'type': 'Section',
                            'title': 'working out',
                            'order': 3.2,
                            'description': 'My working out routine',
                            'children': [
                                {
                                    'type': 'Media',
                                    'description': 'Clip of you doing a quick workout.',
                                    'children': []
                                },
                                {
                                    'type': 'Voiceover',
                                    'description': 'Working out in the morning gives me energy for the entire day.',
                                    'children': []
                                },
                                {
                                    'type': 'Text Overlay',
                                    'description': '‘Energy Boost!’ with a sun emoji.',
                                    'children': []
                                }
                            ]
                        }]
                    },
                    {
                        'type': 'Section',
                        'title': 'Tips',
                        'order': 4,
                        'description': 'Tips for Your Morning Routine',
                        'children': [
                            {
                                'type': 'Section',
                                'title': 'My tips',
                                'order': 4.1,
                                'description': 'My Morning tips',
                                'children': [
                                    {
                                        'type': 'Voiceover',
                                        'description': 'Here are my top tips for creating your own morning routine.',
                                        'children': []
                                    },
                                    {
                                        'type': 'Text Overlay',
                                        'description': '‘Tip 1: Start with a goal in mind.’',
                                        'children': []
                                    },{
                                        'type': 'Media',
                                        'description': 'Clip of you doing your morning tips.',
                                        'children': []
                                    }
                                ]
                            },{
                                'type': 'Section',
                                'title': 'My Goals',
                                'order': 4.2,
                                'description': 'My Morning Goals',
                                'children': [
                                    {
                                        'type': 'Voiceover',
                                        'description': 'Set a clear goal for your morning. This gives you a sense of purpose and direction. Consistency is key. Keep your routine simple and stick with it.',
                                        'children': []
                                    },
                                    {
                                        'type': 'Text Overlay',
                                        'description': '‘Tip 2: Keep it simple and consistent.’',
                                        'children': []
                                    },{
                                        'type': 'Media',
                                        'description': 'Clip of you doing doing your goals.',
                                        'children': []
                                    }
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Call to Action',
                'order': 5,
                'children': [
                    {
                        'type': 'dialog',
                        'description': 'Join me in a 30-day morning routine challenge!'
                    },
                    {
                        'type': 'Text Overlay',
                        'description': '‘#MorningChallenge’ with a call to action to follow your social media handles.'
                    },
                    {
                        'type': 'Media',
                        'description': 'Clip of you talking into the camera.',
                        'children': []
                    }
                ]
            },
            {
                'type': 'Ending',
                'order': 6,
                'children': [
                    {
                        'type': 'dialog',
                        'description': 'Thanks for watching! Don\'t forget to like, comment, and share. Follow me for more tips!',
                        'children': [

                        ]
                    },
                    {
                        'type': 'Background Audio/Sound',
                        'description': 'Upbeat, cheerful music to match the mood.',
                        ]
                    },
                    {
                        'type': 'Media',
                        'description': 'Clip of you speaking directly to the camera and waving bye.',
                        'children': [
                            {
                                'type': 'Note',
                                'description': 'Maintain eye contact with the camera to build connection.',
                                'children': []
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Thumbnail',
                'description': 'A vibrant image of you smiling and holding a coffee mug with text overlay: ‘Start Your Morning Right!’'
            },
            {
                'type': 'Watermark',
                'description': 'Your brand logo in the bottom right corner throughout the video.',
                'children': []
            },
            {
                'type': 'SEO Tags/Hashtags',
                'description': '#MorningRoutine #HealthyHabits',
                'children': []
            }
        ]";


        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $array['model'] ?? "gpt-4o",
            'messages' => [
                ["role" => "system", "content" => "You are a helpful script writer for social media."],
                ["role" => "user", "content" =>$content]],
        'temperature' => $array['temperature'] ?? 1,
        'max_tokens' => $array['max_tokens'] ?? 4000
        ]);

        try {
            return json_decode($response->body())->choices[0]->message->content;
        } catch (\Exception $e) {
            dd(json_decode($response->body()));
        }





        
    }











    public static function GenerateContentFromBlock($data) {

        $elements = SystemScriptElement::all(['label', 'definition']); 

        $resultString = '';

        foreach ($elements as $element) {
            $resultString .= "- {$element->label} ({$element->definition})\n";
        }



        $transcript = '';

        foreach ($data['files'] as $file) {
            $transcriptLine = '';

            // Check if start_time and end_time exist
            if (!empty($file['start_time']) && !empty($file['end_time'])) {
                $transcriptLine .= " - Duration: " . $file['start_time'] . " - " . $file['end_time'];
            }

            // Check if transcript exists
            if (!empty($file['transcript'])) {
                $transcriptLine .= " Transcript: " . $file['transcript'];
            }

            // Check if description exists
            if (!empty($file['description'])) {
                $transcriptLine .= " Description: " . $file['description'];
            }

            // Check if textover exists
            if (!empty($file['textover'])) {
                $transcriptLine .= " Textover: " . $file['textover'];
            }

            // Add the line to the transcript if it's not empty
            if (!empty($transcriptLine)) {
                $transcript .= $transcriptLine . "\n";
            }
        }


        $content = "So these are the sections from a video. Using the following labels(in brackets is the definition) and structure, put it with the things I have given. Use the labels as necessary to write a video script. Ensure in chronological order that I have given. Do not add anything extra that was not in the transcript but just break it down into what you think is best. Remember put transcript under dialog and textover under textover. If possible break down the description into the script. Return the response in JSON format.Do not wrap the json codes in JSON markers. Every voice related or part needs to have some sort of media as sibling. Can't be empty

        Details:
        " . $transcript . "

        Labels:
        ".$resultString."

        Example JSON structure:

        [
            {
                'type': 'Title',
                'title': 'Transform Your Mornings with Me!',
                'description': 'About transforming your morning in a different way',
                'children': []
            },
            {
                'type': 'Intro',
                'order': 1,
                'description': '',
                'children': [
                    {
                        'type': 'Hook',
                        'description': 'Get watchers attention',
                        'children': [  
                            {
                                'type': 'dialog',
                                'description': 'Ever wonder how a great morning routine can change your day?',
                                'children': []
                            },      
                            {
                                'type': 'Media',
                                'description': 'clip of you saying this.',
                                'children': []
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Body',
                'children': [
                    {
                        'type': 'Section',
                        'title': 'Why it matters',
                        'order': 2,
                        'description': 'Description on why it matters etc',
                        'children': [
                            {
                                'type': 'Voiceover',
                                'description': 'Hi, I\'m [Your Name], and today I\'m going to share how a solid morning routine has transformed my life.',
                                'children': []
                            },
                            {
                                'type': 'Media',
                                'description': 'Use a clip of you waking up and starting your day.',
                                'children': []
                            }
                        ]
                    },
                    {
                        'type': 'Section',
                        'title': 'my routine',
                        'order': 3,
                        'description': 'My Morning Routine',
                        'children': [
                        {
                            'type': 'Section',
                            'title': 'step by step',
                            'order': 3.1,
                            'description': 'My Morning Routine',
                            'children': [
                                {
                                    'type': 'Voiceover',
                                    'description': 'Let’s dive into my morning routine step by step.',
                                    'children': []
                                },
                                {
                                    'type': 'Media',
                                    'description': 'Clip of you making a healthy breakfast.',
                                    'children': [
                                        {
                                            'type': 'Note',
                                            'description': 'Mention the benefits of each step.',
                                            'children': []
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            'type': 'Section',
                            'title': 'working out',
                            'order': 3.2,
                            'description': 'My working out routine',
                            'children': [
                                {
                                    'type': 'Media',
                                    'description': 'Clip of you doing a quick workout.',
                                    'children': []
                                },
                                {
                                    'type': 'Voiceover',
                                    'description': 'Working out in the morning gives me energy for the entire day.',
                                    'children': []
                                },
                                {
                                    'type': 'Text Overlay',
                                    'description': '‘Energy Boost!’ with a sun emoji.',
                                    'children': []
                                }
                            ]
                        }]
                    },
                    {
                        'type': 'Section',
                        'title': 'Tips',
                        'order': 4,
                        'description': 'Tips for Your Morning Routine',
                        'children': [
                            {
                                'type': 'Section',
                                'title': 'My tips',
                                'order': 4.1,
                                'description': 'My Morning tips',
                                'children': [
                                    {
                                        'type': 'Voiceover',
                                        'description': 'Here are my top tips for creating your own morning routine.',
                                        'children': []
                                    },
                                    {
                                        'type': 'Text Overlay',
                                        'description': '‘Tip 1: Start with a goal in mind.’',
                                        'children': []
                                    },{
                                        'type': 'Media',
                                        'description': 'Clip of you doing your morning tips.',
                                        'children': []
                                    }
                                ]
                            },{
                                'type': 'Section',
                                'title': 'My Goals',
                                'order': 4.2,
                                'description': 'My Morning Goals',
                                'children': [
                                    {
                                        'type': 'Voiceover',
                                        'description': 'Set a clear goal for your morning. This gives you a sense of purpose and direction. Consistency is key. Keep your routine simple and stick with it.',
                                        'children': []
                                    },
                                    {
                                        'type': 'Text Overlay',
                                        'description': '‘Tip 2: Keep it simple and consistent.’',
                                        'children': []
                                    },{
                                        'type': 'Media',
                                        'description': 'Clip of you doing doing your goals.',
                                        'children': []
                                    }
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Call to Action',
                'order': 5,
                'children': [
                    {
                        'type': 'dialog',
                        'description': 'Join me in a 30-day morning routine challenge!'
                    },
                    {
                        'type': 'Text Overlay',
                        'description': '‘#MorningChallenge’ with a call to action to follow your social media handles.'
                    },
                    {
                        'type': 'Media',
                        'description': 'Clip of you talking into the camera.',
                        'children': []
                    }
                ]
            },
            {
                'type': 'Ending',
                'order': 6,
                'children': [
                    {
                        'type': 'dialog',
                        'description': 'Thanks for watching! Don\'t forget to like, comment, and share. Follow me for more tips!',
                        'children': [

                        ]
                    },
                    {
                        'type': 'Background Audio/Sound',
                        'description': 'Upbeat, cheerful music to match the mood.',
                        ]
                    },
                    {
                        'type': 'Media',
                        'description': 'Clip of you speaking directly to the camera and waving bye.',
                        'children': [
                            {
                                'type': 'Note',
                                'description': 'Maintain eye contact with the camera to build connection.',
                                'children': []
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Thumbnail',
                'description': 'A vibrant image of you smiling and holding a coffee mug with text overlay: ‘Start Your Morning Right!’'
            },
            {
                'type': 'Watermark',
                'description': 'Your brand logo in the bottom right corner throughout the video.',
                'children': []
            },
            {
                'type': 'SEO Tags/Hashtags',
                'description': '#MorningRoutine #HealthyHabits',
                'children': []
            }
        ]";


        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $array['model'] ?? "gpt-4o",
            'messages' => [
                ["role" => "system", "content" => "You are a helpful script writer for social media."],
                ["role" => "user", "content" =>$content]],
        'temperature' => $array['temperature'] ?? 1,
        'max_tokens' => $array['max_tokens'] ?? 4000
        ]);

        try {
            return json_decode($response->body())->choices[0]->message->content;
        } catch (\Exception $e) {
            dd(json_decode($response->body()));
        }





        
    }





































        public static function GenerateContentFromBlock2($data) {

        $elements = SystemScriptElement::all(['label', 'definition']); 

        $resultString = '';

        foreach ($elements as $element) {
            $resultString .= "- {$element->label} ({$element->definition})\n";
        }



        $transcript = '';

        foreach ($data as $file) {
            $transcriptLine = '';

            // Check if start_time and end_time exist
            if (!empty($file['start_time']) && !empty($file['end_time'])) {
                $transcriptLine .= " - Duration: " . $file['start_time'] . " - " . $file['end_time'];
            }


            // Check if description exists
            if (!empty($file['description'])) {
                $transcriptLine .= " Description: " . $file['description'];
            }

    

            // Add the line to the transcript if it's not empty
            if (!empty($transcriptLine)) {
                $transcript .= $transcriptLine . "\n";
            }
        }


        $content = "So these are the sections from a video. Using the following labels(in brackets is the definition) and structure, put it with the things I have given. Use the labels as necessary to write a short funny video script for IG reel you can think of from just using this video. Ensure in chronological order that I have given. Remember put transcript under dialog and textover under textover. If possible break down the description into the script. 2 scenes max. Dont have to use the entire video, smaller sections is fine. The goal is to make a short and engaging video because people's attention spans are low. Have the timestamp for what scenes being used as well. Return the response in JSON format.Do not wrap the json codes in JSON markers. Every voice related or part needs to have some sort of media as sibling. Can't be empty

        Details:
        " . $transcript . "

        Labels:
        ".$resultString."

        Example JSON structure:

        [
            {
                'type': 'Title',
                'title': 'Transform Your Mornings with Me!',
                'description': 'About transforming your morning in a different way',
                'children': []
            },
            {
                'type': 'Intro',
                'order': 1,
                'description': '',
                'children': [
                    {
                        'type': 'Hook',
                        'description': 'Get watchers attention',
                        'children': [  
                            {
                                'type': 'dialog',
                                'description': 'Ever wonder how a great morning routine can change your day?',
                                'children': []
                            },      
                            {
                                'type': 'Media',
                                'description': 'clip of you saying this.',
                                'children': []
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Body',
                'children': [
                    {
                        'type': 'Section',
                        'title': 'Why it matters',
                        'order': 2,
                        'description': 'Description on why it matters etc',
                        'children': [
                            {
                                'type': 'Voiceover',
                                'description': 'Hi, I\'m [Your Name], and today I\'m going to share how a solid morning routine has transformed my life.',
                                'children': []
                            },
                            {
                                'type': 'Media',
                                'description': 'Use a clip of you waking up and starting your day.',
                                'children': []
                            }
                        ]
                    },
                    {
                        'type': 'Section',
                        'title': 'my routine',
                        'order': 3,
                        'description': 'My Morning Routine',
                        'children': [
                        {
                            'type': 'Section',
                            'title': 'step by step',
                            'order': 3.1,
                            'description': 'My Morning Routine',
                            'children': [
                                {
                                    'type': 'Voiceover',
                                    'description': 'Let’s dive into my morning routine step by step.',
                                    'children': []
                                },
                                {
                                    'type': 'Media',
                                    'description': 'Clip of you making a healthy breakfast.',
                                    'children': [
                                        {
                                            'type': 'Note',
                                            'description': 'Mention the benefits of each step.',
                                            'children': []
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            'type': 'Section',
                            'title': 'working out',
                            'order': 3.2,
                            'description': 'My working out routine',
                            'children': [
                                {
                                    'type': 'Media',
                                    'description': 'Clip of you doing a quick workout.',
                                    'children': []
                                },
                                {
                                    'type': 'Voiceover',
                                    'description': 'Working out in the morning gives me energy for the entire day.',
                                    'children': []
                                },
                                {
                                    'type': 'Text Overlay',
                                    'description': '‘Energy Boost!’ with a sun emoji.',
                                    'children': []
                                }
                            ]
                        }]
                    },
                    {
                        'type': 'Section',
                        'title': 'Tips',
                        'order': 4,
                        'description': 'Tips for Your Morning Routine',
                        'children': [
                            {
                                'type': 'Section',
                                'title': 'My tips',
                                'order': 4.1,
                                'description': 'My Morning tips',
                                'children': [
                                    {
                                        'type': 'Voiceover',
                                        'description': 'Here are my top tips for creating your own morning routine.',
                                        'children': []
                                    },
                                    {
                                        'type': 'Text Overlay',
                                        'description': '‘Tip 1: Start with a goal in mind.’',
                                        'children': []
                                    },{
                                        'type': 'Media',
                                        'description': 'Clip of you doing your morning tips.',
                                        'children': []
                                    }
                                ]
                            },{
                                'type': 'Section',
                                'title': 'My Goals',
                                'order': 4.2,
                                'description': 'My Morning Goals',
                                'children': [
                                    {
                                        'type': 'Voiceover',
                                        'description': 'Set a clear goal for your morning. This gives you a sense of purpose and direction. Consistency is key. Keep your routine simple and stick with it.',
                                        'children': []
                                    },
                                    {
                                        'type': 'Text Overlay',
                                        'description': '‘Tip 2: Keep it simple and consistent.’',
                                        'children': []
                                    },{
                                        'type': 'Media',
                                        'description': 'Clip of you doing doing your goals.',
                                        'children': []
                                    }
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Call to Action',
                'order': 5,
                'children': [
                    {
                        'type': 'dialog',
                        'description': 'Join me in a 30-day morning routine challenge!'
                    },
                    {
                        'type': 'Text Overlay',
                        'description': '‘#MorningChallenge’ with a call to action to follow your social media handles.'
                    },
                    {
                        'type': 'Media',
                        'description': 'Clip of you talking into the camera.',
                        'children': []
                    }
                ]
            },
            {
                'type': 'Ending',
                'order': 6,
                'children': [
                    {
                        'type': 'dialog',
                        'description': 'Thanks for watching! Don\'t forget to like, comment, and share. Follow me for more tips!',
                        'children': [

                        ]
                    },
                    {
                        'type': 'Background Audio/Sound',
                        'description': 'Upbeat, cheerful music to match the mood.',
                        ]
                    },
                    {
                        'type': 'Media',
                        'description': 'Clip of you speaking directly to the camera and waving bye.',
                        'children': [
                            {
                                'type': 'Note',
                                'description': 'Maintain eye contact with the camera to build connection.',
                                'children': []
                            }
                        ]
                    }
                ]
            },
            {
                'type': 'Thumbnail',
                'description': 'A vibrant image of you smiling and holding a coffee mug with text overlay: ‘Start Your Morning Right!’'
            },
            {
                'type': 'Watermark',
                'description': 'Your brand logo in the bottom right corner throughout the video.',
                'children': []
            },
            {
                'type': 'SEO Tags/Hashtags',
                'description': '#MorningRoutine #HealthyHabits',
                'children': []
            }
        ]";


        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $array['model'] ?? "gpt-4o",
            'messages' => [
                ["role" => "system", "content" => "You are a helpful script writer for social media."],
                ["role" => "user", "content" =>$content]],
        'temperature' => $array['temperature'] ?? 1,
        'max_tokens' => $array['max_tokens'] ?? 4000
        ]);

        try {
            return json_decode($response->body())->choices[0]->message->content;
        } catch (\Exception $e) {
            dd(json_decode($response->body()));
        }





        
    }






















    public static function GenerateContentTest($data) {

        $elements = SystemScriptElement::all(['label', 'definition']); 



        $content = "Make ffmpeg commands and put them array and only send it in JSON structure starting with the sections and textover based on this data - {$data}. Example and make sure to use all the directory ex Return the response in JSON format.Do not wrap the json codes in JSON markers.

            Example
            [
            {
                'sections': [
                    '-i C:\mpp\htdocs\storwy\storage\app\public\media\intro.mp4 -c:v libx264 -c:a aac -af silencedetect=noise=-40dB:d=0.2 -f null - 2>&1 | findstr /r /c:silence > C:\mpp\htdocs\storwy\storage\app\public\output\intro_silence.txt',
                    '-i C:\xampp\htdocs\storwy\storage\app/public\media/C-MD-793470da-8d8c-4d02-8161-32b0156ce59e.mp4 -c:v libx264 -c:a aac -af silencedetect=noise=-40dB:d=0.2 -f null - 2>&1 | findstr /r /c:silence > C:\xampp\htdocs\storwy\storage\app/public\output/file.txt'
                ],
                'textarea': [
                    '-i C:\mpp\htdocs\storwy\storage\app\public\media\intro.mp4 -c:v libx264 -c:a aac -af silencedetect=noise=-40dB:d=0.2 -f null - 2>&1 | findstr /r /c:silence > C:\mpp\htdocs\storwy\storage\app\public\output\intro_silence.txt',
                    '-i C:\xampp\htdocs\storwy\storage\app/public\media/C-MD-793470da-8d8c-4d02-8161-32b0156ce59e.mp4 -c:v libx264 -c:a aac -af silencedetect=noise=-40dB:d=0.2 -f null - 2>&1 | findstr /r /c:silence > C:\xampp\htdocs\storwy\storage\app/public\output/file.txt'
                ]
            }
            ]

        ";


        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $array['model'] ?? "gpt-4o",
            'messages' => [
                ["role" => "system", "content" => "You are a helpful script writer for social media."],
                ["role" => "user", "content" =>$content]],
        'temperature' => $array['temperature'] ?? 1,
        'max_tokens' => $array['max_tokens'] ?? 4000
        ]);

        try {
            return json_decode($response->body())->choices[0]->message->content;
        } catch (\Exception $e) {
            dd(json_decode($response->body()));
        }





        
    }







    public static function GenerateContentTest2($data) {




        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $array['model'] ?? "gpt-4o",
            'messages' => [
                ["role" => "system", "content" => "You are a expert story writer."],
                ["role" => "user", "content" =>$data]],
        'temperature' => $array['temperature'] ?? 1,
        'max_tokens' => $array['max_tokens'] ?? 4096
        ]);

        try {
            return json_decode($response->body())->choices[0]->message->content;
        } catch (\Exception $e) {
            dd(json_decode($response->body()));
        }





        
    }







}