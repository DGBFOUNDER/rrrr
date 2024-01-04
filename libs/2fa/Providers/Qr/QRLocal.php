<?php

namespace RobThree\Auth\Providers\Qr;

require_once dirname(__FILE__).'/../../phpqrcode.php';                 // Yeah, we're gonna need that

class QRLocal implements IQRCodeProvider {
  public function getMimeType() {
    return 'image/png';                             // This provider only returns PNG's
  }

  public function getQRCodeImage($qrtext, $size) {
    ob_start();                                     // 'Catch' QRCode's output
    QRcode::png($qrtext, null, QR_ECLEVEL_L, 5, 2); // We ignore $size and set it to 3
                                                    // since phpqrcode doesn't support
                                                    // a size in pixels...
    $result = ob_get_contents();                    // 'Catch' QRCode's output
    ob_end_clean();                                 // Cleanup
    return $result;                                 // Return image
  }
}

?>