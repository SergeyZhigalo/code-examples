<?php

namespace Foundation\Enums;

use Foundation\Traits\GetEnumValues;

enum ExampleEnum: string
{
    use GetEnumValues;

    case Foo = 'foo';
    case Bar = 'bar';

    public function name(): string
    {
        return match($this) {
            self::Foo => 'фоо',
            self::Bar => 'бар',
        };
    }

    public static function getName(string $value): ?string
    {
        return self::tryFrom($value)?->name();
    }

    public static function toArray(): array
    {
        return array_combine(
            array_map(fn ($case) => $case->value, self::cases()),
            array_map(fn ($case) => $case->name(), self::cases())
        );
    }
}
