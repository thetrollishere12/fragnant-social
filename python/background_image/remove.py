import sys
import os
import io
from rembg import remove
from PIL import Image, ImageEnhance

def remove_background(input_path, output_path):
    # Ensure absolute paths
    input_path = os.path.abspath(input_path)
    output_path = os.path.abspath(output_path)

    print(f"Processing: {input_path}")

    if not os.path.exists(input_path):
        print(f"Error: File '{input_path}' not found.")
        return

    try:
        with open(input_path, "rb") as file:
            input_image = file.read()
    except Exception as e:
        print(f"Error opening file: {e}")
        return

    # Remove background
    output_image = remove(input_image)

    # Convert the processed image back into an editable format
    img = Image.open(io.BytesIO(output_image)).convert("RGBA")

    # Increase sharpness for a crispier result
    enhancer = ImageEnhance.Sharpness(img)
    img = enhancer.enhance(3.0)  # Increase sharpness (Tweakable: 2.0 - 4.0)

    # Save with high quality
    img.save(output_path, format="PNG", quality=100)

    print(f"Background removed and saved as {output_path}")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python main.py <input_image> <output_image>")
        sys.exit(1)

    input_image_path = sys.argv[1]
    output_image_path = sys.argv[2]

    remove_background(input_image_path, output_image_path)