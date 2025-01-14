<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

trait HasUserIDTrait
{
	/**
	 * @var int
	 */
	protected int $userID;

	/**
	 * @return int
	 */
	public function userID(): int
	{
		return $this->userID;
	}
}
