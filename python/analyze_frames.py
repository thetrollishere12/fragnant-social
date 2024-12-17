import cv2
import os
import sys
import json
from skimage.metrics import structural_similarity as ssim

def analyze_frames(frames_dir, pixel_count_threshold=50000, ssim_threshold=0.97, intensity_threshold=100):
    """
    Analyze frames in the given directory to detect significant changes.

    :param frames_dir: Directory containing frames
    :param pixel_count_threshold: Higher threshold for non-zero pixel changes
    :param ssim_threshold: Higher SSIM threshold for structural similarity
    :param intensity_threshold: Intensity difference threshold for pixel changes
    :return: List of frames with significant changes
    """
    # Get all frame files sorted by filename (frame sequence)
    frames = sorted([os.path.join(frames_dir, f) for f in os.listdir(frames_dir) if f.endswith('.jpg')])

    if len(frames) < 2:
        print("Error: Not enough frames to analyze.")
        return []

    significant_changes = []
    prev_frame = cv2.imread(frames[0], cv2.IMREAD_GRAYSCALE)

    for i in range(1, len(frames)):
        curr_frame = cv2.imread(frames[i], cv2.IMREAD_GRAYSCALE)

        # Compute the absolute difference between the two frames
        diff = cv2.absdiff(prev_frame, curr_frame)
        
        # Apply intensity threshold to the difference
        _, binary_diff = cv2.threshold(diff, intensity_threshold, 255, cv2.THRESH_BINARY)
        non_zero_count = cv2.countNonZero(binary_diff)

        # Calculate SSIM between frames
        score, _ = ssim(prev_frame, curr_frame, full=True)

        # Check if the difference exceeds stricter thresholds
        if non_zero_count > pixel_count_threshold and score < ssim_threshold:
            significant_changes.append({
                "frame": frames[i],
                "non_zero_count": non_zero_count,
                "ssim_score": score
            })

        prev_frame = curr_frame

    return significant_changes


if __name__ == "__main__":
    # Get the frames directory from the command line arguments
    if len(sys.argv) < 2:
        print("Usage: python analyze_frames.py <frames_directory> [pixel_count_threshold] [ssim_threshold] [intensity_threshold]")
        sys.exit(1)

    frames_directory = sys.argv[1]
    pixel_count_threshold = int(sys.argv[2]) if len(sys.argv) > 2 else 50000
    ssim_threshold = float(sys.argv[3]) if len(sys.argv) > 3 else 0.97
    intensity_threshold = int(sys.argv[4]) if len(sys.argv) > 4 else 100

    # Analyze the frames
    changes = analyze_frames(frames_directory, pixel_count_threshold, ssim_threshold, intensity_threshold)

    # Output the results as JSON
    print(json.dumps(changes, indent=4))