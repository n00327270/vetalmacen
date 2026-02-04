<?php
require_once __DIR__ . '/../config/azure_config.php';
require_once __DIR__ . '/../app/models/Database.php';

class AzureBlobHelper {
    
    private $accountName;
    private $accountKey;
    private $containerName;
    private $blobEndpoint;
    private static $cachedExpiryMinutes = null;

    public function __construct() {
        $this->accountName = AZURE_STORAGE_ACCOUNT_NAME;
        $this->accountKey = AZURE_STORAGE_ACCOUNT_KEY;
        $this->containerName = AZURE_STORAGE_CONTAINER_NAME;
        $this->blobEndpoint = AZURE_STORAGE_URL . $this->containerName . '/';
    }

    /**
     * Subir imagen a Azure Blob Storage
     * @param string $filePath - Ruta del archivo temporal
     * @param string $blobName - Nombre único del blob (ej: producto_123456.jpg)
     * @return array - ['success' => bool, 'url' => string, 'blobName' => string, 'error' => string]
     */
    public function uploadImage($filePath, $blobName) {
        try {
            // Leer el contenido del archivo
            $fileContent = file_get_contents($filePath);
            
            if ($fileContent === false) {
                return [
                    'success' => false,
                    'error' => 'No se pudo leer el archivo'
                ];
            }

            // Obtener el tipo MIME del archivo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);

            // Construir la URL del blob
            $blobUrl = $this->blobEndpoint . $blobName;

            // Fecha RFC1123 para el header
            $date = gmdate('D, d M Y H:i:s T');

            // Preparar los headers
            $contentLength = strlen($fileContent);
            
            $canonicalizedHeaders = "x-ms-blob-type:BlockBlob\nx-ms-date:{$date}\nx-ms-version:2020-10-02\n";
            $canonicalizedResource = "/{$this->accountName}/{$this->containerName}/{$blobName}";

            // String to sign
            $stringToSign = "PUT\n\n\n{$contentLength}\n\n{$mimeType}\n\n\n\n\n\n\n{$canonicalizedHeaders}{$canonicalizedResource}";

            // Crear firma
            $signature = base64_encode(hash_hmac('sha256', utf8_encode($stringToSign), base64_decode($this->accountKey), true));

            // Headers para la petición
            $headers = [
                "x-ms-date: {$date}",
                "x-ms-version: 2020-10-02",
                "x-ms-blob-type: BlockBlob",
                "Content-Type: {$mimeType}",
                "Content-Length: {$contentLength}",
                "Authorization: SharedKey {$this->accountName}:{$signature}"
            ];

            // Hacer la petición con cURL
            $ch = curl_init($blobUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                
                return [
                    'success' => false,
                    'error' => 'Error de cURL: ' . $error
                ];
            }
            
            curl_close($ch);

            // Verificar código de respuesta
            if ($httpCode == 201) {
                return [
                    'success' => true,
                    'url' => $blobUrl,
                    'blobName' => $blobName
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al subir archivo. Código HTTP: ' . $httpCode,
                    'response' => $response
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar imagen de Azure Blob Storage
     * @param string $blobName - Nombre del blob a eliminar
     * @return array - ['success' => bool, 'error' => string]
     */
    public function deleteImage($blobName) {
        try {
            if (empty($blobName)) {
                return ['success' => false, 'error' => 'BlobName vacío'];
            }

            // Construir la URL del blob
            $blobUrl = $this->blobEndpoint . $blobName;

            // Fecha RFC1123
            $date = gmdate('D, d M Y H:i:s T');

            $canonicalizedHeaders = "x-ms-date:{$date}\nx-ms-version:2020-10-02\n";
            $canonicalizedResource = "/{$this->accountName}/{$this->containerName}/{$blobName}";

            // String to sign para DELETE
            $stringToSign = "DELETE\n\n\n\n\n\n\n\n\n\n\n\n{$canonicalizedHeaders}{$canonicalizedResource}";

            // Crear firma
            $signature = base64_encode(hash_hmac('sha256', utf8_encode($stringToSign), base64_decode($this->accountKey), true));

            // Headers
            $headers = [
                "x-ms-date: {$date}",
                "x-ms-version: 2020-10-02",
                "Authorization: SharedKey {$this->accountName}:{$signature}"
            ];

            // Petición DELETE con cURL
            $ch = curl_init($blobUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 202) {
                return ['success' => true];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al eliminar. Código HTTP: ' . $httpCode
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar nombre único para el blob
     * @param string $originalFilename - Nombre original del archivo
     * @return string - Nombre único generado
     */
    public function generateUniqueBlobName($originalFilename) {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return "producto_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Obtener minutos de expiración desde mastertable (IdMasterTable = 201)
     * Se cachea en memoria para no consultar la BD cada vez
     */
    private function getExpiryMinutes() {
        if (self::$cachedExpiryMinutes !== null) {
            return self::$cachedExpiryMinutes;
        }

        try {
            $database = new Database();
            $conn = $database->getConnection();

            $query = "SELECT Value FROM mastertable WHERE IdMasterTable = 201 AND States = 1";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();

            self::$cachedExpiryMinutes = $result ? (int)$result['Value'] : 60;
        } catch (Exception $e) {
            error_log("Error al leer expiración de mastertable: " . $e->getMessage());
            self::$cachedExpiryMinutes = 60; // fallback si falla
        }

        return self::$cachedExpiryMinutes;
    }

    /**
     * Generar URL con SAS Token (Shared Access Signature) para acceso temporal
     * @param string $blobName - Nombre del blob
     * @param int $expiryMinutes - Minutos de validez (default: 60)
     * @return string - URL completa con SAS token
     */
    public function generateBlobSASUrl($blobName, $expiryMinutes = null) {
        if ($expiryMinutes === null) {
            $expiryMinutes = $this->getExpiryMinutes();
        }
        try {
            // URL base del blob
            $blobUrl = $this->blobEndpoint . $blobName;
            
            // Fecha de inicio y expiración (formato ISO 8601)
            $start = gmdate('Y-m-d\TH:i:s\Z', time() - 300); // 5 min antes
            $expiry = gmdate('Y-m-d\TH:i:s\Z', time() + ($expiryMinutes * 60));
            
            // Parámetros SAS
            $signedPermissions = 'r'; // read only
            $signedStart = $start;
            $signedExpiry = $expiry;
            $canonicalizedResource = '/blob/' . $this->accountName . '/' . $this->containerName . '/' . $blobName;
            $signedIdentifier = '';
            $signedIP = '';
            $signedProtocol = 'https';
            $signedVersion = '2020-10-02';
            $signedResource = 'b'; // blob
            $signedSnapshotTime = '';
            $rscc = ''; // Cache-Control
            $rscd = ''; // Content-Disposition
            $rsce = ''; // Content-Encoding
            $rscl = ''; // Content-Language
            $rsct = ''; // Content-Type
            
            // String to sign para Azure Blob SAS v2
            // Orden EXACTO según documentación Microsoft:
            $stringToSign = implode("\n", [
                $signedPermissions,
                $signedStart,
                $signedExpiry,
                $canonicalizedResource,
                $signedIdentifier,
                $signedIP,
                $signedProtocol,
                $signedVersion,
                $signedResource,
                $signedSnapshotTime,
                $rscc,
                $rscd,
                $rsce,
                $rscl,
                $rsct
            ]);
            
            // Generar firma (sin utf8_encode, ya no es necesario en PHP 8+)
            $signature = base64_encode(
                hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true)
            );
            
            // Construir parámetros SAS
            $sasParams = [
                'sp' => $signedPermissions,
                'st' => $signedStart,
                'se' => $signedExpiry,
                'spr' => $signedProtocol,
                'sv' => $signedVersion,
                'sr' => $signedResource,
                'sig' => $signature
            ];
            
            // Construir query string
            $sasQueryString = http_build_query($sasParams);
            
            // URL completa con SAS token
            return $blobUrl . '?' . $sasQueryString;
            
        } catch (Exception $e) {
            // Log del error
            error_log('Error generando SAS token: ' . $e->getMessage());
            
            // Fallback: retornar URL base (no funcionará en contenedor privado)
            return $this->blobEndpoint . $blobName;
        }
    }
}