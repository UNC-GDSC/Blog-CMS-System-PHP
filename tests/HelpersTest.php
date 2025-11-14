<?php

/**
 * Unit Tests for Helper Classes
 * Run with: php tests/HelpersTest.php
 *
 * Note: For production, use PHPUnit for proper test execution
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Helpers\Validator;
use App\Helpers\SEO;

class HelpersTest
{
    private $passed = 0;
    private $failed = 0;

    public function run()
    {
        echo "Running Helper Tests...\n\n";

        $this->testValidator();
        $this->testSEO();

        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Results: {$this->passed} passed, {$this->failed} failed\n";
        echo str_repeat("=", 50) . "\n";

        return $this->failed === 0 ? 0 : 1;
    }

    private function testValidator()
    {
        echo "Testing Validator...\n";

        // Test required validation
        $validator = new Validator(['name' => '']);
        $validator->rule('name', 'required', 'Name');
        $this->assert(!$validator->passes(), 'Required field validation fails on empty value');

        $validator = new Validator(['name' => 'John']);
        $validator->rule('name', 'required', 'Name');
        $this->assert($validator->passes(), 'Required field validation passes on non-empty value');

        // Test email validation
        $validator = new Validator(['email' => 'invalid']);
        $validator->rule('email', 'email', 'Email');
        $this->assert(!$validator->passes(), 'Email validation fails on invalid email');

        $validator = new Validator(['email' => 'test@example.com']);
        $validator->rule('email', 'email', 'Email');
        $this->assert($validator->passes(), 'Email validation passes on valid email');

        // Test min/max validation
        $validator = new Validator(['text' => 'ab']);
        $validator->rule('text', 'min:3', 'Text');
        $this->assert(!$validator->passes(), 'Min length validation fails when too short');

        $validator = new Validator(['text' => 'abc']);
        $validator->rule('text', 'min:3', 'Text');
        $this->assert($validator->passes(), 'Min length validation passes when long enough');

        // Test sanitization
        $dirty = '<script>alert("xss")</script>Hello';
        $clean = Validator::sanitize($dirty);
        $this->assert(strpos($clean, '<script>') === false, 'Sanitize removes HTML tags');
    }

    private function testSEO()
    {
        echo "\nTesting SEO...\n";

        // Test slug generation
        $slug = SEO::generateSlug('Hello World! This is a Test');
        $this->assert($slug === 'hello-world-this-is-a-test', 'Slug generation works correctly');

        // Test description generation
        $content = 'This is a long piece of content. ' . str_repeat('Lorem ipsum dolor sit amet. ', 20);
        $description = SEO::generateDescription($content, 160);
        $this->assert(strlen($description) <= 163, 'Description is within length limit'); // 160 + "..."

        // Test reading time calculation
        $content = str_repeat('word ', 400); // 400 words
        $readTime = SEO::calculateReadingTime($content, 200); // 200 words per minute
        $this->assert($readTime === 2, 'Reading time calculation is correct');

        // Test keyword extraction
        $content = 'PHP is great. PHP is amazing. PHP is wonderful. I love PHP programming.';
        $keywords = SEO::extractKeywords($content, 5);
        $this->assert(in_array('php', $keywords), 'Keyword extraction finds common words');
    }

    private function assert($condition, $message)
    {
        if ($condition) {
            echo "  ✓ $message\n";
            $this->passed++;
        } else {
            echo "  ✗ $message\n";
            $this->failed++;
        }
    }
}

// Run tests
$test = new HelpersTest();
exit($test->run());
