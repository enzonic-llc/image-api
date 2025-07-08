# Image API (PHP)

## Features

*   **Upload Images**: Upload images (JPG, PNG, JPEG, GIF) to the `images/` directory.
*   **Download Images**: Download images by their filename.
*   **Delete Images**: Delete images by their filename.
*   **CORS Enabled**: Configured to allow cross-origin requests.

## Setup

1.  **Prerequisites**:
    *   PHP (7.4 or higher recommended)
    *   A web server (e.g., Apache, Nginx) with PHP support.
    *   PHP `curl` extension enabled.
    *   PHP `sqlite3` extension enabled.

2.  **Clone the repository (or create the files manually)**:
    ```bash
    git clone <repository_url>
    cd image-api
    ```
    If you created the files manually, ensure `index.php` and the `images/` directory are in your web server's document root.


4.  **Initialize SQLite Database**:
    The `client_sessions.sqlite` database file will be automatically created and initialized by `client_auth.php` if it doesn't exist. Ensure the web server has write permissions in the directory where `client_sessions.sqlite` will be created.

5.  **Ensure `images` directory exists and is writable**:
    The `images` directory should be in the same location as `index.php`. Make sure your web server has write permissions to this directory.

    ```bash
    # On Linux/macOS
    chmod -R 777 images/
    ```
    On Windows, ensure the IIS/Apache user has modify permissions for the `images` folder.

## API Endpoints

All endpoints are relative to the `index.php` file. If your `index.php` is accessible at `http://localhost/image-api/index.php`, then:

**Authentication**: All API requests (POST, GET, DELETE) require an API key passed in the `Authorization` header as a Bearer token.
Example: `Authorization: Bearer your_super_secret_api_key`

### 1. Upload Image (POST)

*   **URL**: `/index.php`
*   **Method**: `POST`
*   **Content-Type**: `multipart/form-data`
*   **Headers**:
    *   `Authorization`: `Bearer <your_api_key>`
*   **Parameters**:
    *   `image`: The image file to upload.
*   **Example (using curl)**:
    ```bash
    curl -X POST -H "Authorization: Bearer your_super_secret_api_key" -F "image=@/path/to/your/image.jpg" http://localhost/image-api/index.php
    ```
*   **Success Response**:
    ```json
    {
        "message": "Image uploaded successfully.",
        "fileName": "your_image.jpg"
    }
    ```
*   **Error Responses**:
    *   `401 Unauthorized`: "Unauthorized: Invalid or missing API Key."
    *   `400 Bad Request`: "No image file provided." or "Invalid file type. Only JPG, JPEG, PNG, GIF are allowed."
    *   `500 Internal Server Error`: "Failed to upload image."

### 2. Download Image (GET)

*   **URL**: `/index.php?name={filename}`
*   **Method**: `GET`
*   **Headers**:
    *   `Authorization`: `Bearer <your_api_key>`
*   **Parameters**:
    *   `name`: The name of the image file to download (e.g., `your_image.jpg`).
*   **Example (using curl)**:
    ```bash
    curl -X GET -H "Authorization: Bearer your_super_secret_api_key" http://localhost/image-api/index.php?name=your_image.jpg -o downloaded_image.jpg
    ```
*   **Success Response**: The image file will be downloaded.
*   **Error Response**:
    *   `401 Unauthorized`: "Unauthorized: Invalid or missing API Key."
    *   `404 Not Found`: "Image not found."
    *   `400 Bad Request`: "No image name provided."

### 3. Delete Image (DELETE)

*   **URL**: `/index.php`
*   **Method**: `DELETE`
*   **Content-Type**: `application/x-www-form-urlencoded` (or `application/json` if you modify the PHP to parse JSON)
*   **Headers**:
    *   `Authorization`: `Bearer <your_api_key>`
*   **Parameters**:
    *   `name`: The name of the image file to delete.
*   **Example (using curl)**:
    ```bash
    curl -X DELETE -H "Authorization: Bearer your_super_secret_api_key" -d "name=your_image.jpg" http://localhost/image-api/index.php
    ```
*   **Success Response**:
    ```json
    {
        "message": "Image deleted successfully.",
        "fileName": "your_image.jpg"
    }
    ```
*   **Error Responses**:
    *   `401 Unauthorized`: "Unauthorized: Invalid or missing API Key."
    *   `404 Not Found`: "Image not found."
    *   `500 Internal Server Error`: "Failed to delete image."
    *   `400 Bad Request`: "No image name provided."

## CORS Configuration

The API is configured to allow requests from any origin (`Access-Control-Allow-Origin: *`). It also allows `GET`, `POST`, `DELETE`, and `OPTIONS` methods and `Content-Type`, `Authorization` headers.

If you need to restrict access to specific origins, change `*` to your allowed origin(s) in the `.env` file.
