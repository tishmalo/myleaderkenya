<?php

namespace App\Http\Requests\Admin;

/**
 * Identical rules to PollStoreRequest; a separate class keeps the store and
 * update paths free to diverge later without touching shared behaviour.
 */
class PollUpdateRequest extends PollStoreRequest {}
