<div id="banner_container">
    <div id="banner"
         style="background-color: <?php echo $sponsor->getBackgroundColor() ?>; color: <?php echo $sponsor->getFontColor() ?>">
        <?php if ($sponsor->getLogo()) { ?>
            <?php if ($url = $sponsor->getUrl()) { ?>
                <a href="<?php echo $url; ?>">
            <?php } ?>
            <div id="logo" class="d-flex flex-wrap align-items-center">
                <img src="<?php echo $sponsor->getLogo() ?>" alt="<?php echo $sponsor->getName() ?>"/>
            </div>
            <?php if ($url) { ?>
                </a>
            <?php } ?>
        <?php } ?>
        <div id="body" class="">
            <?php if ($url = $sponsor->getUrl()) { ?>
            <a href="<?php echo $url; ?>" style="color: <?php echo $sponsor->getFontColor() ?>">
                <?php } ?>
                <h2><?php echo $sponsor->getName() ?>&nbsp;</h2>
                <?php if ($url) { ?>
            </a>
        <?php } ?>
            <?php if ($slogan = $sponsor->getBusinessPitch()) { ?>
                <?php if (strlen($slogan) > 36) { ?>

                    <marquee class="li" direction=”right” onmouseover="stop()"
                             onmouseout="start()"><?php echo $slogan ?></marquee>

                <?php } else { ?>
                    <p><?php echo $slogan ?></p>
                <?php } ?>
            <?php } ?>

        </div>
        <div id="contact_data"
             class="<?php
             $bannerPhone = $sponsor->getPhone();
             $bannerAddress = $sponsor->getAddress();
             $bannerZipcode = $sponsor->getZipcode();

             if ($bannerPhone && $bannerAddress) echo ''; else echo '' ?>">
            <?php if ($bannerPhone) { ?>
                <a href="tel:<?php echo $bannerPhone ?>"><i class="bi bi-telephone-fill"></i><!-- <span
                            class="d-none d-sm-inline"><?php echo $bannerPhone; ?></span>--></a>
            <?php } ?>
            <?php if ($bannerAddress && $bannerZipcode) {
                $zipcodeData = $sponsor->getZipcodeData();
                ?>
                <a href="http://maps.google.com/?q=<?php echo $bannerAddress ?>, <?php echo ucfirst($zipcodeData['city']) ?>, <?php echo ucfirst($zipcodeData['state']) ?> <?php echo $bannerZipcode ?>, USA">
                    &nbsp;<i class="bi bi-geo-alt-fill"></i><!--<span
                            class="d-none d-sm-inline"><?php echo $bannerAddress; ?></span>-->
                </a>
            <?php } ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    <?php if (strlen($slogan) > 36) { ?>


    /* Vanilla JS */

    var rightJS = {
        init: function () {
            rightJS.Tags = document.querySelectorAll('.rightJS');
            for (var i = 0; i < rightJS.Tags.length; i++) {
                rightJS.Tags[i].style.overflow = 'hidden';
            }
            rightJS.Tags = document.querySelectorAll('.rightJS div');
            for (var i = 0; i < rightJS.Tags.length; i++) {
                rightJS.Tags[i].style.position = 'relative';
                rightJS.Tags[i].style.right = '-' + rightJS.Tags[i].parentElement.offsetWidth + 'px';
            }
            rightJS.loop();
        },
        loop: function () {
            for (var i = 0; i < rightJS.Tags.length; i++) {
                var x = parseFloat(rightJS.Tags[i].style.right);
                x++;
                var W = rightJS.Tags[i].parentElement.offsetWidth;
                var w = rightJS.Tags[i].offsetWidth;
                if ((x / 100) * W > w) x = -W;
                if (rightJS.Tags[i].parentElement.parentElement.querySelector(':hover') !== rightJS.Tags[i].parentElement) rightJS.Tags[i].style.right = x + 'px';
            }
            requestAnimationFrame(this.loop.bind(this));
        }
    };
    window.addEventListener('load', rightJS.init);


    <?php } ?>
</script>
