<?php

function tlgbIsPremium() {
    return tlgb_fs()->can_use_premium_code() ?? false;
}
