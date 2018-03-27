<?php
/**
 * Created by: Jens
 * Date: 27-3-2018
 */

namespace CloudControl\Cms\cc;


class ResponseHeaders
{
    const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN_CONTENT = '*';
    const HEADER_CACHE_CONTROL = 'Cache-Control';
    const HEADER_CACHE_CONTROL_CONTENT_NO_STORE_NO_CACHE_MUST_REVALIDATE_MAX_AGE_0 = 'no-store, no-cache, must-revalidate, max-age=0';
    const HEADER_CONNECTION = 'Connection';
    const HEADER_CONNECTION_CONTENT_KEEP_ALIVE = 'Keep-Alive';
    const HEADER_CONTENT_DESCRIPTION = 'Content-Description';
    const HEADER_CONTENT_DESCRIPTION_CONTENT = 'File Transfer';
    const HEADER_CONTENT_DISPOSITION = 'Content-Disposition';
    const HEADER_CONTENT_LENGTH = 'Content-Length';
    const HEADER_CONTENT_SECURITY_POLICY = 'Content-Security-Policy';
    const HEADER_CONTENT_SECURITY_POLICY_CONTENT_INSECURE = 'default-src \'self\' https: \'unsafe-inline\' \'unsafe-eval\'';
    const HEADER_CONTENT_SECURITY_POLICY_CONTENT_LOCALHOST = 'default-src * \'unsafe-inline\' \'unsafe-eval\' data: blob:';
    const HEADER_CONTENT_SECURITY_POLICY_CONTENT_SECURE = 'default-src https: \'unsafe-inline\' \'unsafe-eval\'';
    const HEADER_CONTENT_TRANSFER_ENCODING = 'Content-Transfer-Encoding';
    const HEADER_CONTENT_TRANSFER_ENCODING_CONTENT_BINARY = 'binary';
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON = 'application/json';
    const HEADER_CONTENT_TYPE_CONTENT_TEXT_HTML = 'text/html';
    const HEADER_EXPIRES = 'Expires';
    const HEADER_PRAGMA = 'Pragma';
    const HEADER_PRAGMA_CONTENT_CACHE = 'cache';
    const HEADER_PRAGMA_CONTENT_NO_CACHE = 'no-cache';
    const HEADER_PRAGMA_CONTENT_PUBLIC = 'public';
    const HEADER_REFERRER_POLICY = 'Referrer-Policy';
    const HEADER_REFERRER_POLICY_CONTENT = 'strict-origin-when-cross-origin';
    const HEADER_SET_COOKIE = 'Set-Cookie';
    const HEADER_STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    const HEADER_STRICT_TRANSPORT_SECURITY_CONTENT = 'max-age=31536000';
    const HEADER_X_CONTENT_SECURITY_POLICY = 'X-Content-Security-Policy'; // For IE
    const HEADER_X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    const HEADER_X_CONTENT_TYPE_OPTIONS_CONTENT = 'nosniff;';
    const HEADER_X_FRAME_OPTIONS = 'X-Frame-Options: ';
    const HEADER_X_FRAME_OPTIONS_CONTENT = 'SAMEORIGIN';
    const HEADER_X_POWERED_BY = 'X-Powered-By';
    const HEADER_X_POWERED_BY_CONTENT = 'Cloud Control - https://getcloudcontrol.org';
    const HEADER_X_XSS_PROTECTION = 'X-XSS-Protection';
    const HEADER_X_XSS_PROTECTION_CONTENT = '1; mode=block';

    /**
     * @var array
     */
    protected static $headers = array(
        self::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN => self::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN_CONTENT,
        self::HEADER_CONTENT_TYPE => self::HEADER_CONTENT_TYPE_CONTENT_TEXT_HTML,
        self::HEADER_REFERRER_POLICY => self::HEADER_REFERRER_POLICY_CONTENT,
        self::HEADER_STRICT_TRANSPORT_SECURITY => self::HEADER_STRICT_TRANSPORT_SECURITY_CONTENT,
        self::HEADER_X_CONTENT_TYPE_OPTIONS => self::HEADER_X_CONTENT_TYPE_OPTIONS_CONTENT,
        self::HEADER_X_FRAME_OPTIONS => self::HEADER_X_FRAME_OPTIONS_CONTENT,
        self::HEADER_X_POWERED_BY => self::HEADER_X_POWERED_BY_CONTENT,
        self::HEADER_X_XSS_PROTECTION => self::HEADER_X_XSS_PROTECTION_CONTENT,
    );

    protected static $initialized = false;

    /**
     * ResponseHeaders constructor.
     */
    public function __construct()
    {
        self::init();
    }

    /**
     * Adds content security policy headers
     */
    public static function init()
    {
        self::add(self::HEADER_SET_COOKIE, '__Host-sess=' . session_id() . '; path=' . Request::$subfolders . '; Secure; HttpOnly; SameSite;');
        if (Request::isSecure()) {
            self::add(self::HEADER_CONTENT_SECURITY_POLICY, self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_SECURE);
            self::add(self::HEADER_STRICT_TRANSPORT_SECURITY, self::HEADER_STRICT_TRANSPORT_SECURITY_CONTENT);
            self::add(self::HEADER_X_CONTENT_SECURITY_POLICY, self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_SECURE);
        } elseif (Request::isLocalhost()) {
            self::add(self::HEADER_CONTENT_SECURITY_POLICY, self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_LOCALHOST);
            self::add(self::HEADER_X_CONTENT_SECURITY_POLICY, self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_LOCALHOST);
        } else {
            self::add(self::HEADER_CONTENT_SECURITY_POLICY, self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_INSECURE);
            self::add(self::HEADER_X_CONTENT_SECURITY_POLICY, self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_INSECURE);
        }
        self::$initialized = true;
    }

    /**
     * @param $headerName
     * @param $headerContent
     */
    public static function add($headerName, $headerContent)
    {
        self::$headers[$headerName] = $headerContent;
    }

    /**
     * @param $headerName
     */
    public static function delete($headerName)
    {
        if (isset(self::$headers[$headerName])) {
            unset(self::$headers[$headerName]);
        }
    }

    /**
     * @return array
     */
    public static function getHeaders()
    {
        return self::$headers;
    }

    public static function sendAllHeaders()
    {
        foreach (self::$headers as $headerName => $headerContent) {
            header($headerName . ': ' . $headerContent);
        }
    }
}