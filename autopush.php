<?php


class AutoPush
{
    /**
     * Directory for which the autopush should 
     * look for changes in the files in it
     */
    public $directory = __DIR__;

    /**
     * Set the interval for which autopush 
     * should evaluating the code for pushing
     * 
     */
    public $interval = 5;
    
    

    public $previous = [];

    public function __construct($directory)
    {
        $this->directory = $directory;
        $this->previous = $this->getFileModificationTimes();
    }
    
    /**
     * Listens for changes awaiting after a 
     * certain interval of time
     * 
     */
    public function listen()
    {
        $this->log("Listening for changes in directory: {$this->directory}");
        while (true) {
            sleep($this->interval);
            if (!$this->onChange()) continue;

            echo chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';
            $this->push();
        }
    }

    /**
     * handles the changes that have been detected
     * 
     * @return bool
     */
    public function onChange()
    {
        $current = $this->getFileModificationTimes();
        if ($this->previous == $current) return false;

        $this->previous = $current;
        return true;
    }

    /**
     * Keeps track of the time all the files that were changed
     * 
     * @return array 
     */
    private function getFileModificationTimes()
    {
        $modTimes = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $modTimes[$filePath] = filemtime($filePath);
            }
        }

        return $modTimes;
    }

    /**
     * Pushes the changes to git 
     * 
     *   @return object
     *   
     * */
    public function push()
    {
        $changes = $this->getChanges();
        $message = $this->generateCommitMessage($changes);

        $escapedMessage = escapeshellarg($message);

        $this->log(shell_exec("git add *"));
        $this->log(shell_exec("git commit -m '$escapedMessage'")); 
        $this->log(shell_exec("git push origin main"));
        return $this;
    }

    /**
     * Retrieve the changes between the working directory and the last commit.
     */
    private function getChanges()
    {
        return shell_exec("git diff --staged");
    }



    /**
     * Use Gemini to generate a meaningful commit message based on changes.
     *
     * @param string $changes The code changes to analyze.
     * @return string The generated commit message (or empty string on error).
     */
    private function generateCommitMessage($changes)
    {
        $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyCGXPvFjKJ3sX4ryy92-NvfFKK5qQ8Rmd4';

        $fileChanges = shell_exec("git status --short");

       
        $purpose = $this->detectPurpose($fileChanges, $changes);

        
        $prompt = "Generate a concise Git commit message for the following changes:\n\n"
            . "Files changed:\n$fileChanges\n"
            . "Summary of changes: $purpose\n\n"
            . "Detailed changes:\n$changes";

        $data = array(
            "contents" => array(
                "parts" => array(
                    array(
                        "text" => $prompt
                    )
                )
            )

        );

        $ch = curl_init($apiEndpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if (empty($response)) {

            return '';
        }

        $result = json_decode($response, true);


        return $result["candidates"][0]["content"]["parts"][0]["text"];
    }

    /**
     * Detects the purposed based on the 
     * types of file changes that
     * occured     * 
     * @param $fileChanges
     * 
     * 
     * @return string
     */
    private function detectPurpose($fileChanges)
    {
        
        if (stripos($fileChanges, 'add') !== false) {
            return "Add new features or files.";
        } elseif (stripos($fileChanges, 'delete') !== false) {
            return "Remove files or functionality.";
        } elseif (stripos($fileChanges, 'modify') !== false) {
            return "Modify existing code.";
        } else {
            return "General code changes.";
        }
    }

    /**
     * log something to the console
     * 
     */
    private function log($message)
    {
        echo "AutoPush: $message \n\n";
        return $this;
    }
}

$autoPush = new AutoPush(__DIR__);
$autoPush->listen();
