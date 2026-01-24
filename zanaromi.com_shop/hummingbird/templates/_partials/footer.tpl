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
              <img src="/img/x-images/pay/white/gopay-colorfull2.svg" width="60" height="40" alt="GoPay">
              <img src="/img/x-images/pay/white/visa-light-mode.svg" width="60" height="40" alt="Visa">
              <img src="/img/x-images/pay/white/mastercard.svg" width="60" height="40" alt="Maestro">
              <img src="/img/x-images/pay/white/google-pay-light-mode.svg" width="60" height="40" alt="Google Pay">
              <img src="/img/x-images/pay/white/apple-pay-light-mode.svg" width="60" height="40" alt="Apple Pay">
              
    </p>
  </div>
</div>
