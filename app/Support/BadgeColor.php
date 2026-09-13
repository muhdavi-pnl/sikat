<?php

namespace App\Support;

class BadgeColor
{
	public static function classFor(string $group, ?string $value): string
	{
		$default = (string) config('badges.default', 'badge-secondary');
		$map = config('badges.' . $group, []);

		if (!is_array($map)) {
			return $default;
		}

		$key = strtolower(trim((string) $value));

		if ($key === '' || !array_key_exists($key, $map)) {
			return $default;
		}

		return (string) $map[$key];
	}

	public static function role(?string $name): string
	{
		return self::classFor('role', $name);
	}

	public static function guard(?string $name): string
	{
		return self::classFor('guard', $name);
	}

	public static function userStatus(?string $label): string
	{
		return self::classFor('user_status', $label);
	}
}

