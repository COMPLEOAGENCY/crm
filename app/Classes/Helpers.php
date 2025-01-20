<?php
// Path: src/app/Classes/Helpers.php
namespace  Classes;

use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;


if (!function_exists('randProb')) {
    function randProb(array $items)
    {
        $totalProbability = 0; // This is defined to keep track of the total amount of entries

        foreach ($items as $item => $probability) {
            $totalProbability += (int)$probability;
        }

        $stopAt = rand(0, $totalProbability); // This picks a random entry to select
        $currentProbability = 0; // The current entry count, when this reaches $stopAt the winner is chosen

        foreach ($items as $item => $probability) { // Go through each possible item
            $currentProbability += (int)$probability; // Add the probability to our $currentProbability tracker
            if ($currentProbability >= $stopAt) { // When we reach the $stopAt variable, we have found our winner
                return $item;
            }
        }

        return null;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, array $queryParams = [], int $statusCode = 302)
    {
        if (!empty($queryParams)) {
            $queryString = http_build_query($queryParams);
            $url = strtok($url, '?');
            $url .= '?' . $queryString;
        }
        header('Location: ' . $url, true, $statusCode);
        exit();
    }
}


if (!function_exists('cleanUrl')) {
    function cleanUrl(string $url): string
    {
        // Si l'URL commence par http:// ou https://, extraire le chemin
        if (preg_match('~^(?:https?://[^/]+)(.*)$~i', $url, $matches)) {
            $path = $matches[1];
        } else {
            $path = $url;
        }

        // Nettoie uniquement les caractères dangereux et les doubles slashes
        $clean = str_replace(['"', "'", '//'], ['', '', '/'], $path);

        return $clean;
    }
}

if (!function_exists('redirectToLogin')) {
    function redirectToLogin(?string $returnUrl = null): void
    {
        $params = [];
        if ($returnUrl) {
            // Décode si c'est du JSON
            $url = json_decode($returnUrl, true) ?: $returnUrl;
            $params['request_uri'] = cleanUrl($url);
        }
        redirect(URL_SITE . "/loginuser/", $params);
    }
}

if (!function_exists('redirectByUserType')) {
    function redirectByUserType(string $type): void
    {
        $redirectMap = [
            'admin' => ADM,
            'provider' => AFF,
            'client' => USR,
            'user' => ADM
        ];

        if (isset($redirectMap[$type])) {
            redirect(URL_SITE . '/' . $redirectMap[$type]);
        }
    }
}

if (!function_exists('logIt')) {
    function logIt($string = "", $type = "debug", $params = [])
    {
        $logger = new Logger($type);
        $logRotate = new RotatingFileHandler(APPFOLDER . 'logs/app.log', 3, Logger::INFO);
        $logger->pushHandler($logRotate);
        $logger->info($string, (array)$params);
    }
}

if (!function_exists('get_ip')) {
    function get_ip()
    {
        // IP si internet partag�
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // IP derri�re un proxy
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Sinon : IP normale
        else {
            return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        }
    }
}


if (!function_exists('get_usable_url')) {

    function get_usable_url(string $uri): string
    {
        $uri = str_ireplace('%20', ' ', $uri);
        // $uri = html_entity_decode($uri);
        return $uri;
    }
}


if (!function_exists('get_path_directory')) {

    function get_path_directory(string $uri): string
    {
        $pathComponents = parse_url($uri);
        $path = @$pathComponents['path'];
        $part_url = explode('/', $path);

        array_pop($part_url);
        $folder = "/";
        if (is_array($part_url)) {
            foreach ($part_url as $part) {
                if (!empty($part)) {
                    $folder .= $part . "/";
                }
            }
        }
        return $folder;
    }
}


// Clé de chiffrement (16, 24, ou 32 caractères pour AES-128, AES-192, ou AES-256)
define('ENCRYPTION_KEY', 'comeplaywithme#*comeplaywithme#*');

// Générer un IV déterministe à partir de l'URL
if (!function_exists('generateIV')) {
    function generateIV(string $url): string
    {
        return substr(hash('sha256', $url), 0, openssl_cipher_iv_length('aes-256-cbc'));
    }
}

// Fonction pour crypter l'URL
if (!function_exists('createToken')) {
    function createToken(string $url): string
    {
        // Générer un IV déterministe pour cette URL
        $iv = generateIV($url);

        // Chiffrer l'URL en utilisant AES-256-CBC
        $encrypted = openssl_encrypt($url, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);

        // Encoder l'URL d'origine et le texte chiffré en base64
        $token = base64_encode($url . '::' . $encrypted);

        return $token;
    }
}

// Fonction pour décrypter le token et obtenir l'URL originale
if (!function_exists('decodeToken')) {
    function decodeToken(string $token): string
    {
        // Décoder le token base64 pour obtenir l'URL d'origine et le texte chiffré
        $decoded = base64_decode($token);

        // Séparer l'URL d'origine et le texte chiffré
        list($url, $encrypted) = explode('::', $decoded, 2);

        // Générer l'IV déterministe pour cette URL
        $iv = generateIV($url);

        // Déchiffrer le texte chiffré en utilisant AES-256-CBC
        $decrypted_url = openssl_decrypt($encrypted, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);

        return $decrypted_url;
    }
}



if (!function_exists('get_extension')) {
    function get_extension($path)
    {
        $extension_regex = '/.*[^.\/]+\.([^.\/]+)$/iU';
        preg_match($extension_regex, $path, $matches);
        $file_type = isset($matches[1]) ? $matches[1] : '';
        return trim($file_type);
    }
}
if (!function_exists('getMimeTypeFromExtension')) {
    function getMimeTypeFromExtension($extension)
    {
        $mimeTypes = array(
            'txt' => 'text/plain',
            'html' => 'text/html',
            'htm' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'gz' => 'application/gzip',
            'tar' => 'application/x-tar',
            // Types MIME audio
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac',
            'wav' => 'audio/wav',
            'wma' => 'audio/x-ms-wma',

            // Types MIME vidéo
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',

            // Types MIME images
            'bmp' => 'image/bmp',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',

            // Types MIME sous-titres
            'srt' => 'text/plain',
            'vtt' => 'text/vtt',

        );

        // Convertir l'extension en minuscules pour correspondre à la table
        $extension = strtolower($extension);

        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }

        // Si l'extension n'est pas trouvée, retourner un type MIME par défaut ou false
        return false;
    }
}

if (!function_exists('clean_select')) {
    /**
     * Helper function to clean and format select data for a Blade component.
     *
     * @param array $params
     * @return array
     */
    function clean_select(array $params): array
    {
        // if(is_array($params['options'])) {
        //     $params['options'] = (object)$params['options'];
        // }

        return [
            'label' => $params['label'] ?? '',
            'id' => $params['id'] ?? '',
            'name' => $params['name'] ?? '',
            'options' => (object)$params['options'] ?? [],
            'optionValue' => $params['optionValue'] ?? 'value',
            'optionLabel' => $params['optionLabel'] ?? 'name',
            'selected' => $params['selected'] ?? '',
            'attributes' => $params['attributes'] ?? '',
            'required' => $params['required'] ?? '',
            'defaultOption' => $params['defaultOption'] ?? '',
            'validationClass' => $params['validationClass'] ?? '',
            'errorHTML' => $params['errorHTML'] ?? '',
        ];
    }
}
if(!function_exists('clean_input_text')) {
    /**
     * Helper function to clean and format input text data for a Blade component.
     *
     * @param array $params
     * @return array
     */
    function clean_input_text(array $params): array
    {
        return [
            'label' => $params['label'] ?? '',
            'id' => $params['id'] ?? '',
            'name' => $params['name'] ?? '',
            'placeholder' => $params['placeholder'] ?? '',
            'value' => $params['value'] ?? '',
            'readonly' => $params['readonly'] ?? '',
            'required' => $params['required'] ?? '',
            'validationClass' => $params['validationClass'] ?? '',
            'errorHTML' => $params['errorHTML'] ?? '',
        ];
    }
}

