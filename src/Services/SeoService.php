<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\SiteSettingRepository;

class SeoService
{
    private SiteSettingRepository $settingsRepo;
    private array $settings = [];
    private string $baseUrl;
    
    public function __construct(Database $database)
    {
        $this->settingsRepo = new SiteSettingRepository($database);
        $this->loadSettings();
        $this->baseUrl = $this->getSetting('site_url', 'https://example.com');
    }
    
    private function loadSettings(): void
    {
        $settings = $this->settingsRepo->findPublic();
        foreach ($settings as $setting) {
            $this->settings[$setting['setting_key']] = $this->castValue(
                $setting['setting_value'],
                $setting['setting_type']
            );
        }
    }
    
    private function castValue($value, string $type)
    {
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (float)$value : $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    private function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    public function generateMetaTags(array $config = []): string
    {
        $defaults = [
            'title' => $this->getSetting('site_name', 'Manufacturing Platform'),
            'description' => $this->getSetting('site_description', 'Quality manufacturing solutions'),
            'keywords' => $this->getSetting('site_keywords', ''),
            'image' => $this->getSetting('site_logo', ''),
            'url' => $this->getCurrentUrl(),
            'type' => 'website',
            'locale' => 'en_US',
            'siteName' => $this->getSetting('site_name', 'Manufacturing Platform'),
        ];
        
        $meta = array_merge($defaults, $config);
        
        $html = '';
        
        $html .= sprintf('<title>%s</title>' . "\n", htmlspecialchars($meta['title']));
        $html .= sprintf('<meta name="description" content="%s">' . "\n", htmlspecialchars($meta['description']));
        
        if (!empty($meta['keywords'])) {
            $html .= sprintf('<meta name="keywords" content="%s">' . "\n", htmlspecialchars($meta['keywords']));
        }
        
        $html .= $this->generateOpenGraphTags($meta);
        $html .= $this->generateTwitterCardTags($meta);
        $html .= sprintf('<link rel="canonical" href="%s">' . "\n", htmlspecialchars($meta['url']));
        
        return $html;
    }
    
    public function generateOpenGraphTags(array $meta): string
    {
        $html = '';
        $html .= sprintf('<meta property="og:title" content="%s">' . "\n", htmlspecialchars($meta['title']));
        $html .= sprintf('<meta property="og:description" content="%s">' . "\n", htmlspecialchars($meta['description']));
        $html .= sprintf('<meta property="og:type" content="%s">' . "\n", htmlspecialchars($meta['type']));
        $html .= sprintf('<meta property="og:url" content="%s">' . "\n", htmlspecialchars($meta['url']));
        $html .= sprintf('<meta property="og:site_name" content="%s">' . "\n", htmlspecialchars($meta['siteName']));
        $html .= sprintf('<meta property="og:locale" content="%s">' . "\n", htmlspecialchars($meta['locale']));
        
        if (!empty($meta['image'])) {
            $imageUrl = $this->normalizeImageUrl($meta['image']);
            $html .= sprintf('<meta property="og:image" content="%s">' . "\n", htmlspecialchars($imageUrl));
            $html .= '<meta property="og:image:width" content="1200">' . "\n";
            $html .= '<meta property="og:image:height" content="630">' . "\n";
        }
        
        return $html;
    }
    
    public function generateTwitterCardTags(array $meta): string
    {
        $html = '';
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= sprintf('<meta name="twitter:title" content="%s">' . "\n", htmlspecialchars($meta['title']));
        $html .= sprintf('<meta name="twitter:description" content="%s">' . "\n", htmlspecialchars($meta['description']));
        
        if (!empty($meta['image'])) {
            $imageUrl = $this->normalizeImageUrl($meta['image']);
            $html .= sprintf('<meta name="twitter:image" content="%s">' . "\n", htmlspecialchars($imageUrl));
        }
        
        $twitterHandle = $this->getSetting('twitter_handle', '');
        if (!empty($twitterHandle)) {
            $html .= sprintf('<meta name="twitter:site" content="@%s">' . "\n", htmlspecialchars(ltrim($twitterHandle, '@')));
        }
        
        return $html;
    }
    
    public function generateOrganizationSchema(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->getSetting('site_name', 'Manufacturing Platform'),
            'description' => $this->getSetting('site_description', ''),
            'url' => $this->baseUrl,
        ];
        
        $logo = $this->getSetting('site_logo', '');
        if (!empty($logo)) {
            $schema['logo'] = $this->normalizeImageUrl($logo);
        }
        
        $email = $this->getSetting('contact_email', '');
        if (!empty($email)) {
            $schema['email'] = $email;
        }
        
        $phone = $this->getSetting('contact_phone', '');
        if (!empty($phone)) {
            $schema['telephone'] = $phone;
        }
        
        $address = $this->getSetting('business_address', '');
        if (!empty($address)) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->getSetting('business_street', ''),
                'addressLocality' => $this->getSetting('business_city', ''),
                'addressRegion' => $this->getSetting('business_state', ''),
                'postalCode' => $this->getSetting('business_zip', ''),
                'addressCountry' => $this->getSetting('business_country', 'US'),
            ];
        }
        
        $socialLinks = [];
        $facebook = $this->getSetting('social_facebook', '');
        $twitter = $this->getSetting('social_twitter', '');
        $linkedin = $this->getSetting('social_linkedin', '');
        $instagram = $this->getSetting('social_instagram', '');
        
        if (!empty($facebook)) $socialLinks[] = $facebook;
        if (!empty($twitter)) $socialLinks[] = $twitter;
        if (!empty($linkedin)) $socialLinks[] = $linkedin;
        if (!empty($instagram)) $socialLinks[] = $instagram;
        
        if (!empty($socialLinks)) {
            $schema['sameAs'] = $socialLinks;
        }
        
        return $schema;
    }
    
    public function generateLocalBusinessSchema(): array
    {
        $schema = $this->generateOrganizationSchema();
        $schema['@type'] = 'LocalBusiness';
        
        $hours = $this->getSetting('business_hours', '');
        if (!empty($hours)) {
            $schema['openingHours'] = $hours;
        }
        
        $latitude = $this->getSetting('business_latitude', '');
        $longitude = $this->getSetting('business_longitude', '');
        if (!empty($latitude) && !empty($longitude)) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }
        
        $priceRange = $this->getSetting('business_price_range', '');
        if (!empty($priceRange)) {
            $schema['priceRange'] = $priceRange;
        }
        
        return $schema;
    }
    
    public function generateBreadcrumbSchema(array $breadcrumbs): array
    {
        $itemListElements = [];
        
        foreach ($breadcrumbs as $index => $crumb) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'] ?? null,
            ];
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElements,
        ];
    }
    
    public function generateWebsiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->getSetting('site_name', 'Manufacturing Platform'),
            'description' => $this->getSetting('site_description', ''),
            'url' => $this->baseUrl,
        ];
    }
    
    public function renderJsonLd(array $schema): string
    {
        return sprintf(
            '<script type="application/ld+json">%s</script>' . "\n",
            json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
    
    public function renderMultipleJsonLd(array $schemas): string
    {
        $html = '';
        foreach ($schemas as $schema) {
            $html .= $this->renderJsonLd($schema);
        }
        return $html;
    }
    
    private function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return $protocol . $host . $uri;
    }
    
    private function normalizeImageUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        
        if (str_starts_with($url, '/')) {
            return $this->baseUrl . $url;
        }
        
        return $this->baseUrl . '/' . $url;
    }
}
