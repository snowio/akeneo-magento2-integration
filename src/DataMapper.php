<?php
namespace SnowIO\AkeneoMagento2;

use function Joshdifabio\Transform\identity;
use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\MapValues;
use Joshdifabio\Transform\Transform;
use Joshdifabio\Transform\WithKeys;

abstract class DataMapper
{
    public function getTransform(): Transform
    {
        return MapElements::via($this);
    }

    public function getValueTransform(): Transform
    {
        return MapValues::via($this);
    }

    public function getKvTransform(): Transform
    {
        return WithKeys::of(identity())->then(MapValues::via($this));
    }
}
