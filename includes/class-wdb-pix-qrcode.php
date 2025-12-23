<?php
/**
 * Classe para geração de QR Code PIX
 * Baseado em https://github.com/renatomb/php_qrcode_pix
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.1
 * @created 23/12/2025 09:39
 * @updated 23/12/2025 09:56
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDB_Pix_QRCode {
    
    // Monta o payload PIX no formato EMV (baseado em renatomb/php_qrcode_pix)
    public static function generate_payload($pix_key, $merchant_name, $merchant_city, $amount = '', $txid = '', $description = '') {
        
        // Estrutura do PIX conforme padrão EMV
        $px = array();
        
        // Payload Format Indicator
        $px[0] = '01';
        
        // Merchant Account Information - PIX
        $px[26] = array();
        $px[26][0] = 'br.gov.bcb.pix'; // GUI
        $px[26][1] = self::format_pix_key($pix_key); // Chave PIX
        
        // Descrição (opcional)
        if (!empty($description)) {
            $px[26][2] = self::remove_special_chars(substr($description, 0, 25));
        }
        
        // Merchant Category Code
        $px[52] = '0000';
        
        // Transaction Currency (986 = BRL)
        $px[53] = '986';
        
        // Transaction Amount (opcional)
        if (!empty($amount) && floatval($amount) > 0) {
            $px[54] = floatval($amount);
        }
        
        // Country Code
        $px[58] = 'BR';
        
        // Merchant Name (máximo 25 caracteres)
        $px[59] = self::remove_special_chars(substr($merchant_name, 0, 25));
        
        // Merchant City (máximo 15 caracteres)
        $px[60] = self::remove_special_chars(substr($merchant_city, 0, 15));
        
        // Additional Data Field
        $px[62] = array();
        if (!empty($txid)) {
            $px[62][5] = self::remove_special_chars(substr($txid, 0, 25));
        } else {
            $px[62][5] = '***';
        }
        
        // Monta o payload
        $payload = self::mount_pix($px);
        
        // Adiciona CRC16
        $payload .= '6304';
        $payload .= self::crc16_ccitt($payload);
        
        return $payload;
    }
    
    // Monta recursivamente o payload PIX
    private static function mount_pix($px, $parent_key = null) {
        $ret = '';
        foreach ($px as $k => $v) {
            if (!is_array($v)) {
                if ($k == 54) {
                    $v = number_format($v, 2, '.', '');
                } elseif ($parent_key == 26 && $k == 1) {
                    // Chave PIX - não remover caracteres especiais (pode ser e-mail, telefone com +)
                    $v = trim($v);
                } elseif ($parent_key == 26 && $k == 0) {
                    // GUI - manter como está
                    $v = $v;
                } else {
                    $v = self::remove_special_chars($v);
                }
                $ret .= self::c2($k) . self::cpm($v) . $v;
            } else {
                $content = self::mount_pix($v, $k);
                $ret .= self::c2($k) . self::cpm($content) . $content;
            }
        }
        return $ret;
    }
    
    // Retorna tamanho com 2 dígitos
    private static function cpm($tx) {
        return self::c2(strlen($tx));
    }
    
    // Formata número com 2 dígitos
    private static function c2($input) {
        return str_pad($input, 2, '0', STR_PAD_LEFT);
    }
    
    // Calcula CRC16 CCITT-FALSE (conforme padrão PIX)
    private static function crc16_ccitt($str) {
        $crc = 0xFFFF;
        $strlen = strlen($str);
        
        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= ord(substr($str, $c, 1)) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        
        $hex = $crc & 0xFFFF;
        $hex = dechex($hex);
        $hex = strtoupper($hex);
        $hex = str_pad($hex, 4, '0', STR_PAD_LEFT);
        
        return $hex;
    }
    
    // Formata a chave PIX
    private static function format_pix_key($key) {
        $key = trim($key);
        
        // Se for telefone brasileiro sem código do país, adiciona +55
        if (preg_match('/^[0-9]{10,11}$/', $key)) {
            $key = '+55' . $key;
        }
        
        return $key;
    }
    
    // Remove caracteres especiais e acentos
    private static function remove_special_chars($txt) {
        return preg_replace('/[^A-Za-z0-9 ]/', '', self::remove_accents($txt));
    }
    
    // Remove acentos
    private static function remove_accents($texto) {
        $search = explode(',', 'à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż');
        $replace = explode(',', 'a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z');
        return str_replace($search, $replace, $texto);
    }
    
    // Gera URL do QR Code (usando API qrserver.com - Google Charts foi descontinuado)
    public static function get_qrcode_url($payload, $size = 250) {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($payload);
    }
    
    // Renderiza HTML do QR Code PIX
    public static function render_pix_qrcode($pix_key, $merchant_name, $merchant_city = 'SAO PAULO', $amount = '', $description = '') {
        if (empty($pix_key) || empty($merchant_name)) {
            return '<p class="text-red-500 text-sm text-center p-4">' . __('Configure a Chave PIX e Nome do Titular nas configurações.', 'wp-donate-brasil') . '</p>';
        }
        
        $payload = self::generate_payload($pix_key, $merchant_name, $merchant_city, $amount, '', $description);
        $qrcode_url = self::get_qrcode_url($payload, 250);
        
        ob_start();
        ?>
        <div class="wdb-pix-qrcode-container text-center">
            <div class="wdb-qrcode-wrapper inline-block p-4 bg-white rounded-xl shadow-lg border-2 border-green-200">
                <img src="<?php echo esc_url($qrcode_url); ?>" 
                     alt="QR Code PIX" 
                     class="wdb-qrcode-image mx-auto"
                     loading="lazy"
                     width="250" 
                     height="250"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none;" class="p-4 text-gray-500">
                    <i class="fa-solid fa-qrcode text-4xl mb-2"></i>
                    <p class="text-sm"><?php _e('QR Code indisponível', 'wp-donate-brasil'); ?></p>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fa-solid fa-camera mr-1"></i>
                    <?php _e('Escaneie com seu app do banco', 'wp-donate-brasil'); ?>
                </p>
            </div>
            
            <div class="wdb-pix-copiacola mt-4">
                <label class="block text-sm font-medium text-gray-600 mb-2">
                    <i class="fa-solid fa-copy mr-1"></i>
                    <?php _e('PIX Copia e Cola', 'wp-donate-brasil'); ?>
                </label>
                <div class="flex items-center gap-2 max-w-md mx-auto">
                    <input type="text" readonly value="<?php echo esc_attr($payload); ?>"
                           class="wdb-pix-payload flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono truncate"
                           id="wdb-pix-payload">
                    <button type="button" class="wdb-copy-btn px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all flex items-center gap-2"
                            data-copy="<?php echo esc_attr($payload); ?>">
                        <i class="fa-solid fa-copy"></i>
                        <span class="hidden sm:inline"><?php _e('Copiar', 'wp-donate-brasil'); ?></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
