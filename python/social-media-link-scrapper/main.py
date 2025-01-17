import os
import re
import time
import json
import yt_dlp
import logging
import asyncio
import requests
import instaloader
from PIL import Image
from fpdf import FPDF
from pathlib import Path
from pytube import YouTube
from dotenv import load_dotenv
from selenium import webdriver
from selenium_stealth import stealth
from googleapiclient.discovery import build
from selenium.webdriver.common.by import By
from playwright.async_api import async_playwright
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options

# Setup logging
logging.basicConfig(
    level=logging.DEBUG,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("debug.log"),
        logging.StreamHandler()
    ]
)

# Load environment variables
load_dotenv()
CHROMIUM_PATH_EXEC = os.getenv("CHROME_DRIVER_PATH")
logging.info("Loaded environment variables.")

# Directories
RESULT_DIR = Path("result")
POSTS_DIR = Path("posts")
RESULT_DIR.mkdir(exist_ok=True)
POSTS_DIR.mkdir(exist_ok=True)
logging.info("Directories initialized.")

def save_data(platform, scrape_type, data):
    """Save scraped data to JSON files within platform-specific folders."""
    try:
        platform_result_dir = RESULT_DIR / platform
        platform_result_dir.mkdir(exist_ok=True)

        file_path = platform_result_dir / f"{platform}_{scrape_type}.json"

        if file_path.exists():
            with file_path.open("r") as f:
                existing_data = json.load(f)
        else:
            existing_data = []

        existing_data.append(data)

        with file_path.open("w") as f:
            json.dump(existing_data, f, indent=4)

        logging.info(f"Data successfully saved to {file_path}.")
    except Exception as e:
        logging.error(f"Error saving data to {file_path}: {e}")

def download_youtube_video(link, save_path):
    """
    Downloads a YouTube video to the specified path with yt-dlp.
    Args:
        link (str): The URL of the YouTube video.
        save_path (str): The directory path to save the video.
    """
    try:
        # Ensure the save directory exists
        os.makedirs(save_path, exist_ok=True)
        if not os.access(save_path, os.W_OK):
            logging.error(f"Directory '{save_path}' is not writable.")
            return False

        # yt-dlp options
        ydl_opts = {
            'format': 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best',  # Prioritize mp4 format
            'outtmpl': os.path.join(save_path, '%(title)s.%(ext)s'),  # Save in specified directory
            'progress_hooks': [on_progress_callback],  # Attach progress callback
        }

        # Download the video
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            logging.debug(f"Downloading video from: {link}")
            ydl.download([link])
            logging.info(f"Video downloaded successfully to '{save_path}'")
            return True

    except Exception as e:
        logging.error(f"Error downloading video {link}: {e}")
        return False


def on_progress_callback(d):
    """
    Callback function to show progress.
    Args:
        d (dict): Progress information dictionary from yt-dlp.
    """
    if d['status'] == 'downloading':
        percent = d.get('_percent_str', '0.00%')
        speed = d.get('_speed_str', '0.00KiB/s')
        eta = d.get('eta', 'N/A')
        print(f"Downloading: {percent} at {speed} ETA: {eta}")
    elif d['status'] == 'finished':
        print(f"Download completed: {d.get('filename')}")


def download_insta_post(insta_url, save_path):
    # Ensure the save_path is writable
    os.makedirs(save_path, exist_ok=True)
    if not os.access(save_path, os.W_OK):
        logging.error(f"Directory '{save_path}' is not writable.")
        return False

    loader = instaloader.Instaloader()

    # Extract shortcode from the URL
    try:
        shortcode = insta_url.split("/")[-2]
    except IndexError:
        logging.error("Invalid Instagram URL format.")
        return False

    # Load the Instagram post
    try:
        post = instaloader.Post.from_shortcode(loader.context, shortcode)
    except Exception as e:
        logging.error(f"Failed to load post: {e}")
        return False

    # Handle carousel posts
    if post.typename == 'GraphSidecar':
        save_folder = os.path.join(save_path, "carousels")
        os.makedirs(save_folder, exist_ok=True)

        pdf = FPDF()
        sidecar_nodes = list(post.get_sidecar_nodes())
        total_slides = len(sidecar_nodes)

        for index, slide in enumerate(sidecar_nodes):
            if slide.is_video:
                logging.warning("Carousel contains videos, which cannot be added to PDF. Skipping video.")
                continue

            # Download image
            image_url = slide.display_url
            image_path = os.path.join(save_folder, f"slide_{index}.jpg")
            response = requests.get(image_url, stream=True)
            if response.status_code == 200:
                with open(image_path, 'wb') as file:
                    for chunk in response.iter_content(chunk_size=1024):
                        if chunk:
                            file.write(chunk)
                logging.info(f"Downloaded slide {index + 1} of {total_slides}")
            else:
                logging.error(f"Failed to download slide {index + 1}. Status code: {response.status_code}")
                continue

            # Add image to PDF
            try:
                img = Image.open(image_path)
                img = img.convert('RGB')  # Ensure it's in RGB format
                pdf.add_page()
                pdf.image(image_path, x=10, y=10, w=190)
                img.close()
            except Exception as e:
                logging.error(f"Error processing slide {index + 1}: {e}")
            finally:
                os.remove(image_path)  # Cleanup downloaded image

        pdf_file = os.path.join(save_folder, f"{post.owner_username}_carousel.pdf")
        pdf.output(pdf_file)
        logging.info(f"Carousel downloaded as PDF: {pdf_file}")

    # Handle single image posts
    elif post.typename == 'GraphImage':
        save_folder = os.path.join(save_path, "images")
        os.makedirs(save_folder, exist_ok=True)

        image_url = post.url
        image_path = os.path.join(save_folder, f"{post.owner_username}_image.png")
        response = requests.get(image_url, stream=True)
        if response.status_code == 200:
            with open(image_path, 'wb') as file:
                for chunk in response.iter_content(chunk_size=1024):
                    if chunk:
                        file.write(chunk)
            logging.info(f"Image downloaded as PNG: {image_path}")
        else:
            logging.error(f"Failed to download image. Status code: {response.status_code}")

    # Handle video posts
    elif post.typename == 'GraphVideo':
        save_folder = os.path.join(save_path, "videos")
        os.makedirs(save_folder, exist_ok=True)

        video_url = post.video_url
        video_path = os.path.join(save_folder, f"{post.owner_username}_video.mp4")
        response = requests.get(video_url, stream=True)
        if response.status_code == 200:
            with open(video_path, 'wb') as file:
                for chunk in response.iter_content(chunk_size=1024):
                    if chunk:
                        file.write(chunk)
            logging.info(f"Video downloaded as MP4: {video_path}")
        else:
            logging.error(f"Failed to download video. Status code: {response.status_code}")

    else:
        logging.error("Unsupported post type.")
        return False

    return True

def download_tiktok_video(video_url, save_path):
    """
    Downloads a TikTok video from the given URL and saves it to the specified directory.

    Args:
        video_url (str): URL of the TikTok video.
        save_path (str): Destination directory where the downloaded video will be saved.
    """
    try:
        os.makedirs(save_path, exist_ok=True)
        if not os.access(save_path, os.W_OK):
            logging.error(f"Directory '{save_path}' is not writable.")
            return False
        ydl_opts = {
            'outtmpl': os.path.join(save_path, '%(title).10s.%(ext)s'),  # Limit title to 5 characters
            'format': 'best',
        }
        
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            ydl.download([video_url])
        
        print("Download completed successfully!")

    except Exception as e:
        print("Error:", str(e))

def scrape_tiktok(url, scrape_option):
    chrome_options = Options()
    chrome_options.add_argument("--disable-blink-features=AutomationControlled")

    service = Service(CHROMIUM_PATH_EXEC)
    driver = webdriver.Chrome(service=service, options=chrome_options)

    # Enable stealth mode
    stealth(driver,
            languages=["en-US", "en"],
            vendor="Google Inc.",
            platform="Win32",
            webgl_vendor="Intel Inc.",
            renderer="Intel Iris OpenGL Engine",
            fix_hairline=True,
            )

    print("Navigating to URL:", url)
    driver.get(url)
    print("Page loaded successfully")
    time.sleep(10)

    result = {}

    if scrape_option == 'user':
        result['profile_url'] = url
        result['username'] = driver.find_element(By.CSS_SELECTOR, 'h1[data-e2e="user-title"]').text
        result['nickname'] = driver.find_element(By.CSS_SELECTOR, 'h2[data-e2e="user-subtitle"]').text
        result['following'] = driver.find_element(By.CSS_SELECTOR, 'strong[data-e2e="following-count"]').text
        result['followers'] = driver.find_element(By.CSS_SELECTOR, 'strong[data-e2e="followers-count"]').text
        result['likes'] = driver.find_element(By.CSS_SELECTOR, 'strong[data-e2e="likes-count"]').text

        try:
            result['bio'] = driver.find_element(By.CSS_SELECTOR, 'h2[data-e2e="user-bio"]').text
        except:
            result['bio'] = None

        try:
            result['bio_link'] = driver.find_element(By.CSS_SELECTOR, 'a[data-e2e="user-link"]').text
        except:
            result['bio_link'] = None
        
        save_data("tiktok", "user", result)
        logging.info("Successfully scraped YouTube channel data.")

    elif scrape_option == 'video':
        result['videoLink'] = url
        try:
            result['likes'] = driver.find_element(By.CSS_SELECTOR, 'strong[data-e2e="browse-like-count"]').text
        except:
            try:
                result['likes'] = driver.find_element(By.CSS_SELECTOR, 'strong[data-e2e="like-count"]').text
            except:
                result['likes'] = None

        try:
            result['comments'] = driver.find_element(By.CSS_SELECTOR, 'strong[data-e2e="browse-comment-count"]').text
        except:
            try:
                result['comments'] = driver.find_element(By.CSS_SELECTOR, 'strong[data-e2e="comment-count"]').text
            except:
                result['comments'] = None

        try:
            result['saves'] = driver.find_element(By.CSS_SELECTOR, 'strong[data-e2e="undefined-count"]').text
        except:
            result['saves'] = None
        
        save_path = POSTS_DIR / "tiktok" 
        save_path.mkdir(parents=True, exist_ok=True)

        if download_tiktok_video(url, str(save_path)):
            logging.info(f"Video '{result['title']}' saved to {save_path}")

        save_data("tiktok", "video", result)
        logging.info("Successfully scraped YouTube channel data.")

    driver.quit()
    return result

def scrape_youtube(url, scrape_type):
    """Scrape YouTube channel or video data."""
    try:
        api_key = os.getenv("YOUTUBE_API_KEY")
        if not api_key:
            logging.error("YouTube API key is missing in the environment.")
            return None

        youtube = build("youtube", "v3", developerKey=api_key)
        result = {}

        if scrape_type == "user":
            logging.debug("Scraping YouTube channel data.")

            try:
                # Extract channel ID or username
                if "/channel/" in url:
                    channel_id = url.split("/channel/")[-1].split("/")[0]
                elif "@" in url:
                    # For @username URLs
                    username = url.split("@")[-1].split("/")[0]
                    response = youtube.search().list(
                        part="snippet",
                        q=f"@{username}",
                        type="channel",
                        maxResults=1
                    ).execute()

                    # Ensure response contains items
                    if not response.get("items"):
                        logging.error("No channel data found for the username.")
                        return None

                    channel_id = response["items"][0]["id"]["channelId"]
                else:
                    logging.error("Invalid YouTube URL format.")
                    return None

                if not channel_id:
                    logging.error("Failed to extract channel ID from the URL.")
                    return None

                # Get channel details
                response = youtube.channels().list(part="snippet,statistics", id=channel_id).execute()
                if not response.get("items"):
                    logging.error("No items found in the YouTube API response.")
                    return None

                channel = response["items"][0]
                result = {
                    "username": channel["snippet"]["title"],
                    "description": channel["snippet"]["description"],
                    "subscribers": channel["statistics"]["subscriberCount"],
                    "videoCount": channel["statistics"]["videoCount"],
                    "viewCount": channel["statistics"]["viewCount"],
                    "creationDate": channel["snippet"]["publishedAt"],
                    "country": channel["snippet"].get("country", "N/A")
                }

                # Save the data
                save_data("youtube", "user", result)
                logging.info("Successfully scraped YouTube channel data.")
                return result

            except Exception as e:
                logging.error(f"Error scraping YouTube channel data: {e}")
                return None

        elif scrape_type == "video":
            logging.debug("Scraping YouTube video data.")
            try:
                # Extract video ID and check video type
                if "/shorts/" in url:
                    video_id = url.split("/shorts/")[-1].split("?")[0]
                elif "v=" in url:
                    video_id = url.split("v=")[-1].split("&")[0]
                else:
                    logging.error("Invalid YouTube URL format.")
                    return None

                response = youtube.videos().list(part="snippet,statistics,contentDetails", id=video_id).execute()
                if not response.get("items"):
                    logging.error("No items found in the YouTube API response.")
                    return None

                video = response["items"][0]
                duration = video["contentDetails"]["duration"]
                is_short = "PT" in duration and "M" not in duration and "S" in duration and "60" not in duration
                video_type = "short" if is_short else "long"

                result = {
                    "title": video["snippet"]["title"],
                    "description": video["snippet"]["description"],
                    "views": video["statistics"].get("viewCount", "N/A"),
                    "likes": video["statistics"].get("likeCount", "N/A"),
                    "comments": video["statistics"].get("commentCount", "N/A"),
                    "uploadDate": video["snippet"]["publishedAt"],
                    "video_url": url,
                    "channel_url": f"https://www.youtube.com/channel/{video['snippet']['channelId']}",
                    "type": "Shorts" if is_short else "Long",
                }

                video_title = re.sub(r'[\\/*?:"<>|]', "", result["title"])  # Sanitize filename
                filename = f"{video_title[:50]}_{video_id}.mp4"  # Limit title length

                save_path = POSTS_DIR / "youtube" / video_type
                save_path.mkdir(parents=True, exist_ok=True)

                # Only log and proceed with the result after the dictionary is populated
                if download_youtube_video(url, str(save_path)):
                    logging.info(f"Video '{result['title']}' saved to {save_path}")

                save_data("youtube", scrape_type, result)
                return result

            except Exception as e:
                logging.error(f"Error during YouTube scraping: {e}")
                return None


    except Exception as e:
        logging.error(f"Error during YouTube scraping: {e}")
    return None

def scrape_instagram(scrape_type, url):
    """Scrape Instagram user or post data."""
    logging.debug(f"Scraping Instagram. URL={url}, scrape_type={scrape_type}.")
    loader = instaloader.Instaloader()
    result = {}
    try:
        if scrape_type == "user":
            logging.debug("Scraping Instagram user data.")
            username_match = re.search(r"instagram.com/([^/]+)", url)
            if not username_match:
                logging.error("Invalid Instagram profile URL.")
                return None

            username = username_match.group(1)
            profile = instaloader.Profile.from_username(loader.context, username)
            result = {
                "username": profile.username,
                "profile_url": url,
                "profile_picture": profile.profile_pic_url,
                "posts": profile.mediacount,
                "followers": profile.followers,
                "following": profile.followees,
                "bio": profile.biography,
                "is_private": profile.is_private,
                "is_verified": profile.is_verified,
                "external_url": profile.external_url,
            }
        elif scrape_type == "video":
            logging.debug("Scraping Instagram video data.")
            post_shortcode = re.search(r"/p/([^/]+)/", url)
            if not post_shortcode:
                logging.error("Invalid Instagram post URL.")
                return None

            shortcode = post_shortcode.group(1)
            post = instaloader.Post.from_shortcode(loader.context, shortcode)
            result = {
                "shortcode": shortcode,
                "post_url": url,
                "media_type": post.typename,
                "likes": post.likes,
                "comments": post.comments,
                "date_posted": post.date_utc.isoformat(),
                "poster_username": post.owner_username,
                "caption": post.caption,
                "is_video": post.is_video,
            }

        if result:
            # Save the data
            save_path = POSTS_DIR / "instagram"
            save_path.mkdir(parents=True, exist_ok=True)

            # Download the Instagram post
            if download_insta_post(url, str(save_path)):
                logging.info(f"Post '{result.get('shortcode', 'N/A')}' saved to {save_path}")

            save_data("instagram", scrape_type, result)
            return result
    except Exception as e:
        logging.error(f"Error scraping Instagram: {e}")
    return None

async def main():
    logging.info("Starting main application.")
    print("Choose scrape option ('user' or 'video'):")
    scrape_type = input("Enter your choice: ").strip()

    if scrape_type not in ["user", "video"]:
        logging.error("Invalid scrape option. Exiting.")
        return

    print("Choose a platform:")
    print("1. TikTok")
    print("2. YouTube")
    print("3. Instagram")
    platform_choice = input("Enter your choice (1, 2, or 3): ").strip()

    try:
        if platform_choice == "1":
            url = input("Enter the TikTok URL: ").strip()
            result = scrape_tiktok(url, scrape_type)
            logging.info(f"TikTok scrape result: {result}")

        elif platform_choice == "2":
            url = input("Enter the YouTube URL: ").strip()
            result = scrape_youtube(url, scrape_type)
            logging.info(f"YouTube scrape result: {result}")

        elif platform_choice == "3":
            url = input("Enter the Instagram URL: ").strip()
            result = scrape_instagram(scrape_type, url)
            logging.info(f"Instagram scrape result: {result}")

        else:
            logging.error("Invalid platform choice.")
    except Exception as e:
        logging.error(f"Unexpected error: {e}")

if __name__ == "__main__":
    asyncio.run(main())
