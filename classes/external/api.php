<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_geniai\external;

use local_geniai\local\markdown\parse_markdown;

/**
 * Global api file.
 *
 * @package     local_geniai
 * @copyright   2024 Eduardo Kraus https://eduardokraus.com/
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {
    /**
     * History api function.
     *
     * @param int $courseid
     * @param string $action
     *
     * @return array
     */
    public static function history_api($courseid, $action) {
        if ($action == "clear") {
            $_SESSION["messages-v3-{$courseid}"] = [];
            return [
                "result" => true,
                "content" => "[]",
            ];
        }

        if (isset($_SESSION["messages-v3-{$courseid}"])) {
            $messages = $_SESSION["messages-v3-{$courseid}"];
            unset($messages[0]);
            unset($messages[1]);
            unset($messages[2]);
        } else {
            $messages = [];
        }

        $returnmessage = [];
        foreach ($messages as $message) {

            $parsemarkdown = new parse_markdown();

            if (strpos($message["content"], "<audio") === false) {
                $message["content"] = $parsemarkdown->markdown_text($message["content"]);
            }
            $message["format"] = "html";

            $returnmessage[] = $message;
        }

        return [
            "result" => true,
            "content" => json_encode($returnmessage),
        ];
    }

    /**
     * Chat api function.
     *
     * @param string $message
     * @param int $courseid
     * @param null $audio
     * @param string $lang
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function chat_api($message, $courseid, $audio = null, $lang = "en") {
        global $CFG, $DB, $USER, $SITE;
    
        $scenario = optional_param('scenario', '', PARAM_TEXT); // From dropdown or request.
    
        if (!isset($_SESSION["chatstate-{$courseid}"])) {
            $_SESSION["chatstate-{$courseid}"] = [
                'scenario' => '',
                'turn' => 0
            ];
        }
    
        $chatState = &$_SESSION["chatstate-{$courseid}"];
    
        // Choose new persona if none selected or after 10 turns.
        if (empty($chatState['scenario']) || $chatState['turn'] >= 10) {
            $scenarios = ['anna', 'brianna', 'cathy'];
            $chatState['scenario'] = !empty($scenario) ? $scenario : $scenarios[array_rand($scenarios)];
            $chatState['turn'] = 0;
            $_SESSION["user_msgs-{$courseid}"] = [];    // reset user messages
            $_SESSION["bot_msgs-{$courseid}"] = [];     // reset bot messages
        }
    
        $scenario = $chatState['scenario'];
    
        // Persona setup
        switch ($scenario) {
            case "anna":
                $persona = "Your name is Anna Charles and your daughter, Sarah, is 4 years old and has a 
                diagnosis of Autism. Sarah is just starting pre-kindergarten at a new school.
                She received her diagnosis within the last year. She has been receiving speech therapy 
                since 1-year of age. She currently uses a communication app on an iPad. You are a single
                mother of Sarah. You work two jobs and Sarah spends a lot of time with her grandparents.
                You feel guilty because you want to spend more time with Sarah, but it is difficult with 
                your current employment. You are very overwhelmed with Sarah’s diagnosis and her lack of 
                communication. You believe that the iPad is not working for Sarah and you don’t know how 
                to help her.You’re frustrated and are meeting with your daughter Sarah’s teacher and want 
                to figure out better alternatives for Sarah to communicate effectively using the iPad and
                with her grandparents who have difficulty with technology.";
                break;
            case "brianna":
                $persona = "Your name is Brianna Mitchell and your son, Wesley, in 8-years old.  
                Wesley has severe apraxia. His speech is extremely difficult to understand.  
                He currently uses a small handheld AAC device. 
                Wesley has been receiving AAC services from an outpatient pediatric hospital 
                for the past 2 years.  He also receives 30 minutes of therapy from his school-based SLP. 
                You are the mother of Wesley.  You are married and Wesley is your only son. 
                You emailed your son’s outpatient SLP and asked to meet.  You are frustrated because 
                you have tried to contact the school-SLP but you haven’t received a response.
                You are concerned that your son is socially isolated and is having difficulty
                making friends. Recently, you attended an event at Wesley’s school.  
                While in his classroom, you were able to observe Wesley and his classmates.  
                You noticed that Wesley was often alone and rarely interacted with his peers.  
                At one point, you saw him laugh at a classmate’s joke and try to communicate 
                to his classmates with no success. You’re worried and are meeting with your son’s 
                teacher and want to figure out how Wesley could be more social in making friends,
                how to encourage him to use his device without being embarrassed, 
                and if he will ever be able to use his speech.
                ";
                break;
            case "cathy":
                $persona = "Your name is Cathy Fratner and your son, Charlie, is a 2-year old boy with 
                Down Syndrome. Charlie is not yet talking.  He has an iPad with a communication 
                app that his SLP recommended for him to use about 6 months ago. Charlie has 
                been receiving speech and language services through early intervention.  
                Once a week, his SLP goes to his daycare to provide therapy.  You are the mother 
                of Charlie.  You are newly married and Charlie is your first child.  You met with 
                Charlie’s SLP about 6 months ago.  She spent 2 hours with you and your husband.  
                She introduced a communication app to you and showed you how to work the app.  
                It seemed to make sense when the SLP used it with Charlie, but you always feel 
                lost and frustrated when using the app. Your husband doesn’t think that Charlie 
                should be using his iPad to communicate and that he will talk when he is ready. 
                Now you are worried that Charlie won’t learn how to talk if he keeps using the app 
                in therapy and at home. You’re worried and are meeting with your son’s teacher 
                and want to figure out how Charlie could be use the iPad more regularly, if using 
                the iPad consistently will prevent him in the future, and if you should be 
                concerned that Charlie isn’t talking yet.";
                break;
            default:
                $persona = get_config("local_geniai", "prompt");
        }
    
        // Load or initialize message arrays
        $userMessages = $_SESSION["user_msgs-{$courseid}"] ?? [];
        $botMessages = $_SESSION["bot_msgs-{$courseid}"] ?? [];
    
        $rubric = [
            "greeting" => "1 point if teacher starts with a greeting.",
            "empathy" => "1 points for a statement of empathy.",
            "note_permission" => "1 points if teacher asks to take notes.",
            "presenting_problem" => "1 point for asking 'what brings you in today?'.",
            "duration" => "1 point for asking 'how long has this been a problem?'.",
            "exception" => "1 point for asking 'was this ever not a problem?'.",
            "consultation" => "1 point for asking 'have you spoken to anyone else?'.",
            "wrap_up" => "1 points for asking 'anything else to add?'."
        ];
    
        $userMessages = $_SESSION["user_msgs-{$courseid}"] ?? [];
        $botMessages = $_SESSION["bot_msgs-{$courseid}"] ?? [];
    
        // Create full message history (interleaved)
        $fullContext = [[
            "role" => "system",
            "content" => $persona . "\nOnly respond as the parent. Stay in character. Use at most 4 sentences."
        ]];
    
        $totalTurns = count($userMessages);
        for ($i = 0; $i < $totalTurns; $i++) {
            $fullContext[] = ["role" => "user", "content" => $userMessages[$i]];
            if (isset($botMessages[$i])) {
                $fullContext[] = ["role" => "system", "content" => $botMessages[$i]];
            }
        }
    
        $cleanedMessage = strip_tags(trim($message));
        $moderationPrompt = [
            ["role" => "system", "content" => "You're a moderation AI. Decide if the following message contains profanity, foul language, insults, or inappropriate content. Reply with only 'yes' or 'no'."],
            ["role" => "user", "content" => $cleanedMessage]
        ];
        
        $check = self::chat_completions($moderationPrompt);
        $decision = strtolower(trim($check["choices"][0]["message"]["content"] ?? "no"));
        
        if ($decision === "yes") {
            $_SESSION["user_msgs-{$courseid}"] = [];
            $_SESSION["bot_msgs-{$courseid}"] = [];
            $chatState['turn'] = 0;
            $chatState['scenario'] = '';
        
            return [
                "result" => true,
                "format" => "html",
                "content" => "<strong>Grade - 0 out of 10</strong><br>Your message contains inappropriate language. This session is terminated."
            ];
        }
        
        $userMessages[] = $cleanedMessage;
        $fullContext[] = ["role" => "user", "content" => $cleanedMessage];
    
        $chatState['turn']++;
    
        // Final turn (include rubric evaluation instruction)
        if ($chatState['turn'] === 10) {
            $teacherMessages = $_SESSION["user_msgs-{$courseid}"] ?? [];
        
            $formattedRubric = implode("\n", array_map(
                fn($k, $v) => ucfirst(str_replace("_", " ", $k)) . ": " . $v,
                array_keys($rubric),
                $rubric
            ));
        
            // Clear any previous context: overwrite $fullContext completely
            $fullContext = [
                [
                    "role" => "system",
                    "content" =>
                        "You are evaluating a simulated parent-teacher conversation.\n" .
                        "Below are only the teacher's replies (from role: `user`).\n" .
                        "DO NOT consider any system/parent messages in your evaluation.\n\n" .
                        "Rubric:\n" . $formattedRubric . "\n\n" .
                        "Scoring Instructions:\n" .
                        "- Start from 10 points.\n" .
                        "- Deduct 1 point for each missing rubric item.\n" .
                        "- Deduct 1 point if a reply is vague, irrelevant, or ignores the system's last message.\n" .
                        "- Do not go below 0.\n" .
                        "- Output final result like: Grade - X out of 10\n" .
                        "- Then thank the teacher and end politely."
                ]
            ];
        
            // Add only teacher (user) messages for GPT to evaluate
            foreach ($teacherMessages as $i => $msg) {
                $fullContext[] = [
                    "role" => "user",
                    "content" => "Turn " . ($i + 1) . ": " . $msg
                ];
            }
        }
    
        $gpt = self::chat_completions($fullContext);
    
        if (isset($gpt["error"])) {
            $parsemarkdown = new parse_markdown();
            $content = $parsemarkdown->markdown_text($gpt["error"]["message"]);
            return ["result" => false, "format" => "text", "content" => $content];
        }
    
        if (isset($gpt["choices"][0]["message"]["content"])) {
            $response = $gpt["choices"][0]["message"]["content"];
            $parsemarkdown = new parse_markdown();
            $content = $parsemarkdown->markdown_text($response);
    
            $botMessages[] = $response;
    
            // Update sessions
            $_SESSION["user_msgs-{$courseid}"] = $userMessages;
            $_SESSION["bot_msgs-{$courseid}"] = $botMessages;
    
            // Reset after final turn
            if ($chatState['turn'] >= 10) {
                $chatState['turn'] = 0;
                $chatState['scenario'] = '';
                $_SESSION["user_msgs-{$courseid}"] = [];
                $_SESSION["bot_msgs-{$courseid}"] = [];
            }
    
            return ["result" => true, "format" => "html", "content" => $content];
        }
    
        return ["result" => false, "format" => "text", "content" => "Error..."];
    }

    /**
     * Chat completions function.
     *
     * @param array $messages
     * @param bool $ignoremaxtoken
     *
     * @return mixed
     *
     * @throws \dml_exception
     */
    public static function chat_completions($messages, $ignoremaxtoken = false) {
        global $DB;

        $apikey = get_config("local_geniai", "apikey");
        $model = get_config("local_geniai", "model");
        $maxtokens = get_config("local_geniai", "max_tokens");
        $frequencypenalty = get_config("local_geniai", "frequency_penalty");
        $presencepenalty = get_config("local_geniai", "presence_penalty");

        switch (get_config("local_geniai", "case")) {
            case "creative":
                $temperature = .7;
                $topp = .8;
                break;
            case "balanced":
                $temperature = .5;
                $topp = .7;
                break;
            case "precise":
                $temperature = .0;
                $topp = 1.0;
                break;
            case "exploration":
                $temperature = .8;
                $topp = .9;
                break;
            case "formal":
                $temperature = .3;
                $topp = .6;
                break;
            case "informal":
                $temperature = .7;
                $topp = .8;
                break;
            case "chatbot":
                $temperature = .2;
                $topp = .8;
                break;
            default:
                $temperature = .5;
                $topp = .5;
        }

        $messagesok = [];
        foreach ($messages as $message) {
            $message["content"] = strip_tags($message["content"]);
            $messagesok[] = $message;
        }

        $post = (object)[
            "model" => $model,
            "messages" => $messagesok,
            "temperature" => $temperature,
            "top_p" => $topp,
            "frequency_penalty" => floatval($frequencypenalty),
            "presence_penalty" => floatval($presencepenalty),
        ];

        if (!$ignoremaxtoken) {
            $post->max_tokens = intval($maxtokens);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$apikey}",
        ]);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return [
                "error" => [
                    "message" => "http error: " . curl_error($ch),
                ],
            ];
        }
        curl_close($ch);

        $gpt = json_decode($result, true);

        $usage = (object)[
            "send" => json_encode($post, JSON_PRETTY_PRINT),
            "receive" => $result,
            "model" => $model,
            "prompt_tokens" => intval($gpt["usage"]["prompt_tokens"]),
            "completion_tokens" => intval($gpt["usage"]["completion_tokens"]),
            "timecreated" => time(),
            "datecreated" => date("Y-m-d", time()),
        ];
        try {
            $DB->insert_record("local_geniai_usage", $usage);
        } catch (\dml_exception $e) {
            echo $e->getMessage();
        }

        return $gpt;
    }

    /**
     * Function transcriptions
     *
     * @param string $audio
     *
     * @return array
     * @throws \dml_exception
     */
    private static function transcriptions($audio, $lang) {
        global $CFG;

        $audio = str_replace("data:audio/mp3;base64,", "", $audio);
        $audiodata = base64_decode($audio);
        $filename = uniqid();
        $filepath = "{$CFG->dataroot}/temp/{$filename}.mp3";
        file_put_contents($filepath, $audiodata);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/audio/transcriptions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            "file" => curl_file_create($filepath),
            "model" => "whisper-1",
            "response_format" => "verbose_json",
            "language" => $lang,
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: multipart/form-data",
            "Authorization: Bearer " . get_config("local_geniai", "apikey"),
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result);

        return [
            "text" => $result->text,
            "language" => $result->language,
            "filename" => $filename,
        ];
    }

    /**
     * Function speech
     *
     * @param string $input
     *
     * @return string
     *
     * @throws \dml_exception
     */
    private static function speech($input) {
        global $CFG;

        $json = json_encode((object)[
            "model" => "tts-1",
            "input" => $input,
            "voice" => get_config("local_geniai", "voice"),
            "response_format" => "mp3",
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/audio/speech");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . get_config("local_geniai", "apikey"),
        ]);

        $audiodata = curl_exec($ch);
        curl_close($ch);

        $filename = uniqid();
        $filepath = "{$CFG->dataroot}/temp/{$filename}.mp3";
        file_put_contents($filepath, $audiodata);

        return "{$CFG->wwwroot}/local/geniai/load-audio-temp.php?filename={$filename}";
    }
}
