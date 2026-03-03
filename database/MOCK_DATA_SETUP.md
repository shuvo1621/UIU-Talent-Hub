# Mock Data Setup Guide for UIU TalentHub

## Overview
This guide will help you populate the database with 30 mock posts (10 audio, 10 video, 10 blog) and 10 mock users.

## Step 1: Prepare Media Files

### Audio Files (10 files)
Place your audio files in: `assets/Audios/`
- Rename them as: `audio1.mp3`, `audio2.mp3`, ..., `audio10.mp3`

### Audio Thumbnails (10 images)
Place album covers in: `assets/images/`
- Rename them as: `audio1_thumb.jpg`, `audio2_thumb.jpg`, ..., `audio10_thumb.jpg`
- Recommended size: 500x500px

### Video Files (10 files)
Place your video files in: `assets/Videos/`
- Rename them as: `video1.mp4`, `video2.mp4`, ..., `video10.mp4`

### Video Thumbnails (10 images)
Place video thumbnails in: `assets/images/`
- Rename them as: `video1_thumb.jpg`, `video2_thumb.jpg`, ..., `video10_thumb.jpg`
- Recommended size: 1280x720px (16:9 ratio)

### Blog Images (10 images)
Place blog cover images in: `assets/images/`
- Rename them as: `blog1_thumb.jpg`, `blog2_thumb.jpg`, ..., `blog10_thumb.jpg`
- Recommended size: 1200x630px

## Step 2: Run the SQL Script

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select the `uiu_talenthub` database
3. Click on the "SQL" tab
4. Copy the contents of `database/mock_data.sql`
5. Paste and click "Go" to execute

## Step 3: Verify Data

Run these queries to verify:

```sql
-- Check total users (should be 10 + your existing users)
SELECT COUNT(*) as total_users FROM users;

-- Check posts by type (should be 10 each)
SELECT type, COUNT(*) as count FROM posts GROUP BY type;

-- Check total posts (should be 30)
SELECT COUNT(*) as total_posts FROM posts;

-- Check likes
SELECT COUNT(*) as total_likes FROM likes;
```

## Mock Users Created

All mock users have the password: `password123`

| Name | Student ID | Email |
|------|------------|-------|
| Anika Rahman | 011221045 | anika.rahman@uiu.ac.bd |
| Fahim Ahmed | 011221067 | fahim.ahmed@uiu.ac.bd |
| Tasnia Haque | 011221089 | tasnia.haque@uiu.ac.bd |
| Rafid Islam | 011221102 | rafid.islam@uiu.ac.bd |
| Nusrat Jahan | 011221134 | nusrat.jahan@uiu.ac.bd |
| Sakib Hassan | 011221156 | sakib.hassan@uiu.ac.bd |
| Mehrin Sultana | 011221178 | mehrin.sultana@uiu.ac.bd |
| Tanvir Hossain | 011221190 | tanvir.hossain@uiu.ac.bd |
| Lamia Khan | 011221212 | lamia.khan@uiu.ac.bd |
| Shafin Mahmud | 011221234 | shafin.mahmud@uiu.ac.bd |

## Content Titles Reference

### Audio Posts
1. Monsoon Melodies (Music)
2. Recitation of Nazrul (Poem)
3. Tech Talk: AI in Bangladesh (Podcast)
4. Rabindra Sangeet Cover (Music)
5. The Lost City - Audio Story (Story)
6. Campus Life Podcast (Podcast)
7. Fusion Beats (Music)
8. Poetry Night - Jibanananda Das (Poem)
9. Startup Stories Bangladesh (Podcast)
10. Midnight Jazz Sessions (Music)

### Video Posts
1. UIU Cultural Night 2025
2. Campus Tour - A Day at UIU
3. Short Film: The Last Exam
4. Dance Performance - Kathak Fusion
5. Coding Tutorial: React Basics
6. Documentary: Street Food of Dhaka
7. Band Performance - Original Song
8. Comedy Skit - Hostel Life
9. Time-lapse: Sunset from Rooftop
10. Spoken Word Poetry Performance

### Blog Posts
1. My Journey to UIU
2. The Art of Time Management
3. Exploring Dhaka: A Student's Guide
4. Learning to Code: My First Year
5. Mental Health Matters
6. Photography Tips for Beginners
7. The Power of Networking
8. Sustainable Living on Campus
9. Book Review: The Alchemist
10. Preparing for Job Interviews

## Notes

- All posts have realistic like counts (ranging from 41 to 312)
- Posts are dated from January 2025 with varied timestamps
- Each user is assigned specific posts to maintain authenticity
- Sample likes are included to show engagement
- Blog posts have full text content in the `description` field

## Troubleshooting

**If files don't show up:**
1. Check file paths match exactly (case-sensitive)
2. Verify file permissions (should be readable by Apache)
3. Check that files are in the correct directories
4. Clear browser cache

**If SQL fails:**
1. Make sure database exists
2. Check for duplicate student IDs or emails
3. Verify all required columns exist in tables
