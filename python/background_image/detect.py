import sys
import numpy as np
from PIL import Image

def detect_blank_background(image_path, tolerance=100, blank_threshold=0.70):
    """
    Detects if an image has a uniform (blank) background.
    
    :param image_path: Path to the image file
    :param tolerance: Allowed color variation
    :param blank_threshold: Percentage of background similarity required to be "blank"
    :return: Prints "True" if background is blank, otherwise "False"
    """
    try:
        img = Image.open(image_path).convert("RGB")
        img_np = np.array(img)

        height, width, _ = img_np.shape

        # Get background color from four corners
        corners = np.array([
            img_np[0, 0], img_np[0, width-1],
            img_np[height-1, 0], img_np[height-1, width-1]
        ])
        bg_color = np.mean(corners, axis=0)

        # Compare all pixels to the background color
        diff = np.abs(img_np - bg_color)
        similar_pixels = np.all(diff < tolerance, axis=-1)
        
        similarity_ratio = np.sum(similar_pixels) / (height * width)
        print("True" if similarity_ratio >= blank_threshold else "False")

    except Exception as e:
        print("False")  # If error occurs, assume not blank

if __name__ == "__main__":
    if len(sys.argv) < 2:
        sys.exit(1)

    detect_blank_background(sys.argv[1])