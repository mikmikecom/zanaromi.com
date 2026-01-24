{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
<div class="footer__before">
  {block name='hook_footer_before'}
    {hook h='displayFooterBefore'}
  {/block}
</div>

<div class="footer__main">
  <div class="container">
    <div class="footer__main__top row">
      {block name='hook_footer'}
        {hook h='displayFooter'}
      {/block}
    </div>

    <div class="footer__main__bottom row">
      {block name='hook_footer_after'}
        {hook h='displayFooterAfter'}
      {/block}
    </div>

    <p class="copyright">
      {block name='copyright_link'}
        <a href="mailto:mfink@email.cz" target="_blank" rel="noopener noreferrer nofollow">
          {l s='%copyright% %year% - Powered by %prestashop%' sprintf=['%prestashop%' => 'mikmikecom', '%year%' => 'Y'|date, '%copyright%' => '©'] d='Shop.Theme.Global'}
        </a>
      {/block}

                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

              <!-- platební karty -->
              <img src="https://shop.zanaromi.com/img/x-images/pay/gopay.png" alt="GoPay" height="25" width="auto">&nbsp;&nbsp;
              <img src="https://shop.zanaromi.com/img/x-images/pay/visa.svg" alt="visa" height="45" width="auto">&nbsp;
              <img src="https://shop.zanaromi.com/img/x-images/pay/mastercard.svg" alt="mastercard" height="30" width="auto">&nbsp;
              <img src="https://shop.zanaromi.com/img/x-images/pay/google-pay2.svg" alt="google-pay" height="45" width="auto">&nbsp;&nbsp;
              <img src="https://shop.zanaromi.com/img/x-images/pay/apple-pay2.svg" alt="apple-pay" height="45" width="auto">
              
    </p>
  </div>
</div>
