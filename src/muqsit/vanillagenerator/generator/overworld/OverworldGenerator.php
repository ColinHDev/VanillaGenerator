<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld;

use InvalidArgumentException;
use muqsit\vanillagenerator\generator\Environment;
use muqsit\vanillagenerator\generator\overworld\populator\OverworldPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\SnowPopulator;
use muqsit\vanillagenerator\generator\utils\WorldOctaves;
use muqsit\vanillagenerator\generator\VanillaBiomeGrid;
use muqsit\vanillagenerator\generator\VanillaGenerator;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;

class OverworldGenerator extends VanillaGenerator
{
	/** @var \OverworldGenerator */
	private \OverworldGenerator $generator;

	public function __construct(int $seed, string $preset)
	{
		parent::__construct($seed, Environment::OVERWORLD, null, $preset);
		$this->addPopulators(new OverworldPopulator(), new SnowPopulator());

		$enableUHC = false;

		// The preset example: "isUHC,1:environment,overworld"
		$presets = explode(':', $preset);
		foreach ($presets as $preset) {
			$settings = explode(',', $preset);
			if (count($settings) < 2) {
				throw new InvalidArgumentException("World preset must have a key and a value respectively");
			}

			switch ($settings[0]) {
				case "isUHC":
					$enableUHC = (int)$settings[1] === 1;
					break;
				case "environment":
				case "amplification":
					// TODO: These presets are available in mc-generator but they remain inaccessible for now.
			}
		}

		/** @phpstan-ignore-next-line */
		$this->generator = new \OverworldGenerator($seed, $enableUHC);
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
	{
		$chunk = $world->getChunk($chunkX, $chunkZ);

		$biomeData = $chunk->getBiomeIdArray();
		$pelletedEntries = [];

		foreach ($chunk->getSubChunks() as $y => $subChunk) {
			if (!$subChunk->isEmptyFast()) {
				$pelletedEntries[$y] = $subChunk->getBlockLayers()[0];
			} else {
				$newSubChunk = new SubChunk($subChunk->getEmptyBlockId(), [new PalettedBlockArray($subChunk->getEmptyBlockId())], $subChunk->getBlockSkyLightArray(), $subChunk->getBlockLightArray());
				$chunk->setSubChunk($y, $newSubChunk);

				$pelletedEntries[$y] = $newSubChunk->getBlockLayers()[0];
			}
		}

		$biomes = $this->generator->generateChunk($pelletedEntries, $biomeData, World::chunkHash($chunkX, $chunkZ));

		(function () use ($biomes): void {
			/** @noinspection PhpUndefinedFieldInspection */
			/** @phpstan-ignore-next-line */
			$this->biomeIds = new BiomeArray($biomes);
		})->call($chunk);
	}

	protected function generateChunkData(ChunkManager $world, int $chunk_x, int $chunk_z, VanillaBiomeGrid $grid): void
	{
		// NOOP
	}

	protected function createWorldOctaves(): ?WorldOctaves
	{
		return null;
	}
}