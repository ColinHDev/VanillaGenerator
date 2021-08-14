<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\biomegrid\utils\MapLayerPair;
use muqsit\vanillagenerator\generator\Environment;
use pocketmine\data\bedrock\BiomeIds;
use Random;

abstract class MapLayer
{

	public static function initialize(int $seed, int $environment): MapLayerPair
	{
		if ($environment === Environment::OVERWORLD) {
			return new MapLayerPair(new ConstantBiomeMapLayer($seed, BiomeIds::PLAINS), null);
		}

		if ($environment === Environment::NETHER) {
			return new MapLayerPair(new ConstantBiomeMapLayer($seed, BiomeIds::HELL), null);
		}

		return new MapLayerPair(null, null);
	}

	private Random $random;

	public function __construct(private int $seed)
	{
		$this->random = new Random();
	}

	public function setCoordsSeed(int $x, int $z): void
	{
		$this->random->setSeed($this->seed);
		$this->random->setSeed($x * $this->random->nextInt() + $z * $this->random->nextInt() ^ $this->seed);
	}

	public function nextInt(int $max): int
	{
		return $this->random->nextBoundedInt($max);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $size_x
	 * @param int $size_z
	 *
	 * @return int[]
	 */
	abstract public function generateValues(int $x, int $z, int $size_x, int $size_z): array;
}