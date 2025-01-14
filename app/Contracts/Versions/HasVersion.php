<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Versions;

use App\DTO\Version;

/**
 * Must implement version getter.
 */
interface HasVersion
{
	/**
	 * Return version stored.
	 *
	 * @return Version
	 */
	public function getVersion(): Version;
}