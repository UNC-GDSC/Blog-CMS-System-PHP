<?php

namespace App\Helpers;

/**
 * SEO helper for meta tags and optimization
 */
class SEO
{
    /**
     * Generate meta description
     *
     * @param string $content Content to extract description from
     * @param int $length Maximum length
     * @return string
     */
    public static function generateDescription($content, $length = 160)
    {
        // Strip HTML tags
        $text = strip_tags($content);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Truncate to length
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length);
            $text = substr($text, 0, strrpos($text, ' ')) . '...';
        }

        return $text;
    }

    /**
     * Generate URL-friendly slug
     *
     * @param string $text Text to convert
     * @return string
     */
    public static function generateSlug($text)
    {
        // Convert to lowercase
        $slug = strtolower(trim($text));

        // Replace spaces and special chars with hyphens
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);

        // Remove multiple hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // Trim hyphens from ends
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Generate keywords from content
     *
     * @param string $content Content to extract keywords from
     * @param int $count Number of keywords to extract
     * @return array
     */
    public static function extractKeywords($content, $count = 10)
    {
        // Strip HTML and convert to lowercase
        $text = strtolower(strip_tags($content));

        // Remove punctuation
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        // Split into words
        $words = str_word_count($text, 1);

        // Common words to exclude
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
                      'of', 'as', 'by', 'with', 'from', 'is', 'are', 'was', 'were', 'be',
                      'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will',
                      'would', 'should', 'could', 'may', 'might', 'must', 'can', 'this',
                      'that', 'these', 'those', 'it', 'its', 'i', 'you', 'he', 'she', 'we',
                      'they', 'what', 'which', 'who', 'when', 'where', 'why', 'how'];

        // Filter out stop words and short words
        $words = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        // Count word frequency
        $frequency = array_count_values($words);

        // Sort by frequency
        arsort($frequency);

        // Return top keywords
        return array_slice(array_keys($frequency), 0, $count);
    }

    /**
     * Generate Open Graph meta tags
     *
     * @param array $data ['title', 'description', 'image', 'url', 'type']
     * @return string
     */
    public static function generateOGTags($data)
    {
        $tags = [];

        if (isset($data['title'])) {
            $tags[] = '<meta property="og:title" content="' . htmlspecialchars($data['title']) . '">';
        }

        if (isset($data['description'])) {
            $tags[] = '<meta property="og:description" content="' . htmlspecialchars($data['description']) . '">';
        }

        if (isset($data['image'])) {
            $tags[] = '<meta property="og:image" content="' . htmlspecialchars($data['image']) . '">';
        }

        if (isset($data['url'])) {
            $tags[] = '<meta property="og:url" content="' . htmlspecialchars($data['url']) . '">';
        }

        $tags[] = '<meta property="og:type" content="' . ($data['type'] ?? 'article') . '">';
        $tags[] = '<meta property="og:site_name" content="' . Env::get('APP_NAME', 'Blog CMS') . '">';

        return implode("\n    ", $tags);
    }

    /**
     * Generate Twitter Card meta tags
     *
     * @param array $data ['title', 'description', 'image']
     * @return string
     */
    public static function generateTwitterTags($data)
    {
        $tags = [];

        $tags[] = '<meta name="twitter:card" content="summary_large_image">';

        if (isset($data['title'])) {
            $tags[] = '<meta name="twitter:title" content="' . htmlspecialchars($data['title']) . '">';
        }

        if (isset($data['description'])) {
            $tags[] = '<meta name="twitter:description" content="' . htmlspecialchars($data['description']) . '">';
        }

        if (isset($data['image'])) {
            $tags[] = '<meta name="twitter:image" content="' . htmlspecialchars($data['image']) . '">';
        }

        return implode("\n    ", $tags);
    }

    /**
     * Generate canonical URL tag
     *
     * @param string $url Canonical URL
     * @return string
     */
    public static function canonicalTag($url)
    {
        return '<link rel="canonical" href="' . htmlspecialchars($url) . '">';
    }

    /**
     * Calculate reading time in minutes
     *
     * @param string $content Content text
     * @param int $wordsPerMinute Average reading speed
     * @return int
     */
    public static function calculateReadingTime($content, $wordsPerMinute = 200)
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes = ceil($wordCount / $wordsPerMinute);

        return max(1, $minutes);
    }
}
