<?php

namespace App\Helper;

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class EssentiaHelper
{
	
    /**
     * Converts an MP3 file to WAV format using FFmpeg.
     *
     * @param string $inputPath
     * @param string $outputPath
     * @return bool|string
     */
    public static function convertToWav(string $inputPath, string $outputPath)
    {
        echo "Converting MP3 to WAV...\n";
        $process = new Process([env('FFMPEG_BINARIES'), '-i', $inputPath, $outputPath]);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            echo "Failed to convert MP3 to WAV.\n";
            return false;
        }

        echo "Conversion to WAV completed successfully.\n";
        return $outputPath;
    }

    /**
     * Runs Essentia's streaming_extractor_music binary to analyze an audio file.
     *
     * @param string $inputPath
     * @param string $outputJsonPath
     * @return bool|array
     */
    public static function analyzeAudio(string $inputPath, string $outputJsonPath)
    {
        echo "Running Essentia analysis...\n";
        $process = new Process([env('ESSENTIA_PATH') . 'streaming_extractor_music', $inputPath, $outputJsonPath]);
        
        // Set a higher timeout (e.g., 300 seconds = 5 minutes)
        $process->setTimeout(300);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            echo "Failed to analyze audio with Essentia.\n";
            return false;
        }

        if (!file_exists($outputJsonPath)) {
            echo "Output JSON file not found.\n";
            return false;
        }

        echo "Essentia analysis completed successfully.\n";
        return json_decode(file_get_contents($outputJsonPath), true);
    }

    /**
     * Handles the entire process: conversion to WAV and analysis using Essentia.
     *
     * @param string $filePath
     * @return array|bool
     */
    public static function processAudio(string $filePath)
    {
        echo "Starting audio processing...\n";
        $outputWavPath = str_replace('.mp3', '.wav', $filePath);
        $outputJsonPath = str_replace('.mp3', '.json', $filePath);

        // Convert to WAV if not already in WAV format
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'wav') {
            if (!self::convertToWav($filePath, $outputWavPath)) {
                return ['error' => 'Failed to convert MP3 to WAV.'];
            }
            $filePath = $outputWavPath;
        }

        // Analyze audio using Essentia
        $results = self::analyzeAudio($filePath, $outputJsonPath);

        if (!$results) {
            return ['error' => 'Failed to analyze audio with Essentia.'];
        }

        // Clean up intermediate files
        if (file_exists($outputWavPath)) {
            unlink($outputWavPath);
            echo "Deleted intermediate WAV file.\n";
        }
        if (file_exists($outputJsonPath)) {
            unlink($outputJsonPath);
            echo "Deleted intermediate JSON file.\n";
        }

        echo "Audio processing completed successfully.\n";
        return $results;
    }
}
