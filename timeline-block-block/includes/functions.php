<?php

function tlgbIsPremium() {
    return TLGB_HAS_PRO ? tlgb_fs()->can_use_premium_code() : false;
}
