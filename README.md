# Custom Post Views Tracker for WordPress  
  
A lightweight WordPress plugin that tracks and displays post views with a customizable widget for showing popular posts based on view count.  
  
## Description  
  
Custom Post Views Tracker is a simple yet powerful WordPress plugin that helps you track and display the number of views your posts and pages receive. It includes a customizable widget that allows you to showcase your most popular content based on view counts.  
  
### Features  
  
- Automatic view tracking for posts and pages  
- Bot/crawler detection to prevent false view counts  
- Customizable widget for displaying popular posts  
- Multiple time range options (daily, weekly, monthly)  
- Category filtering  
- Flexible display options (thumbnail, title, date, category, excerpt, view count)  
- Clean and responsive design  
- Performance optimized  
- Translation ready  
  
## Installation  
  
1. Download the plugin zip file  
2. Go to WordPress admin panel > Plugins > Add New  
3. Click "Upload Plugin" and select the downloaded zip file  
4. Click "Install Now" and then "Activate"  
  
## Widget Configuration  
  
The Popular Posts widget can be configured with the following options:  
  
- **Title**: Widget title  
- **Time Range**: Filter posts by daily, weekly, or monthly views  
- **Category**: Filter posts by specific category  
- **Number of Posts**: Choose how many posts to display  
- **Display Options**:  
  - Thumbnail  
  - Title  
  - Date  
  - Category  
  - Excerpt  
  - Views Count  
  
## Usage  
  
### Basic Usage  
The plugin automatically starts tracking views once activated. View counts are displayed at the bottom of your posts and pages.  
  
### Widget Usage  
1. Go to Appearance > Widgets  
2. Find "Popular Posts Tracker" widget  
3. Drag it to your desired widget area  
4. Configure the settings as needed  
  
### Shortcode (Coming Soon)
```php  
[popular_posts count="5" timerange="weekly" category="1" show="thumbnail,title,date"]
