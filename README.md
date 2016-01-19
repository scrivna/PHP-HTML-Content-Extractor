# PHP HTML Content Extractor
PHP library for extracting useful "content" & keywords from an HTML document, this isn't designed to return readable content, but to extract as much content as possible for further analysis (keyword extraction etc)

## Usage
```php
$ce = new ContentExtractor;
$content_text = $ce->getTextFromHTML($some_html);
```
