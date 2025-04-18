<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\BooleanRequireSupportRule;
use App\Rules\PasswordRule;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SetAlbumProtectionPolicyRequest extends BaseApiRequest implements HasAbstractAlbum, HasPassword
{
	use HasAbstractAlbumTrait;
	use HasPasswordTrait;

	protected bool $is_password_provided;
	protected AlbumProtectionPolicy $album_protection_policy;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if ($this->album instanceof BaseSmartAlbum) {
			return Auth::user()?->may_administrate === true;
		}

		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(true)],
			RequestAttribute::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
			RequestAttribute::IS_LINK_REQUIRED_ATTRIBUTE => 'required|boolean',
			RequestAttribute::IS_NSFW_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE => ['required', 'boolean', new BooleanRequireSupportRule(false, $this->verify)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->album_factory->findAbstractAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);
		$this->album_protection_policy = new AlbumProtectionPolicy(
			is_public: static::toBoolean($values[RequestAttribute::IS_PUBLIC_ATTRIBUTE]),
			is_link_required: static::toBoolean($values[RequestAttribute::IS_LINK_REQUIRED_ATTRIBUTE]),
			is_nsfw: static::toBoolean($values[RequestAttribute::IS_NSFW_ATTRIBUTE]),
			grants_full_photo_access: static::toBoolean($values[RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE]),
			grants_download: static::toBoolean($values[RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE]),
			grants_upload: static::toBoolean($values[RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE]),
		);
		$this->is_password_provided = array_key_exists(RequestAttribute::PASSWORD_ATTRIBUTE, $values);
		$this->password = $this->is_password_provided ? $values[RequestAttribute::PASSWORD_ATTRIBUTE] : null;
	}

	/**
	 * @return AlbumProtectionPolicy
	 */
	public function albumProtectionPolicy(): AlbumProtectionPolicy
	{
		return $this->album_protection_policy;
	}

	public function isPasswordProvided(): bool
	{
		return $this->is_password_provided;
	}
}
