<?php

namespace Hhz\Region;

Class Region
{
	private const REGION_CONFIG_FILE = "region.json";
	private const MAP_FILE = "map.json";

	public static array $regionData = [];
	public static array $mapData = [];

	// 地区代码分隔符
	private const REGION_CODE_SEPARATOR = ',';

	private static array $municipality = ['重庆', '上海', '天津', '北京'];

	// 直辖市
	private static array $directCityCodes = ['110000', '500000', '310000', '120000'];
	// 一国两制
	private static array $directAreaCodes = ['810000', '820000'];
	public static array $specialCites = ['110000', '500000', '310000', '120000', '810000', '820000'];

	/**
	 * 获取区域数据
	 *
	 * @throws Exception
	 */
	protected static function getRegionData()
	{
		if (empty(self::$regionData)) {
			$file = __DIR__ . "/" . self::REGION_CONFIG_FILE;
			if (!file_exists($file)) {
				throw new \RuntimeException('地区配置文件不存在');
			}
			$regionConfig = file_get_contents($file);
			self::$regionData = json_decode($regionConfig, true);
		}
	}

	/**
	 * 获取所有区域信息
	 * @return array
	 * @throws Exception
	 */
	public static function getAllRegion(): array
	{
		self::getRegionData();
		return self::$regionData;
	}

	/**
	 * 获取map数据
	 *
	 * @throws Exception
	 */
	protected static function getMapData(): void
	{
		if (empty(self::$mapData)) {
			$file = __DIR__ . "/" . self::MAP_FILE;
			if (!file_exists($file)) {
				throw new Exception('地区映射文件不存在');
			}

			$mapConfig = file_get_contents($file);
			self::$mapData = json_decode($mapConfig, true);
		}
	}

	/**
	 * 判断某个地区是否为直辖市
	 *
	 * @param $region_name
	 * @return bool
	 */
	public static function isMunicipality($region_name): bool
	{
		return in_array($region_name, self::$municipality, false);
	}

	/**
	 * @throws Exception
	 */
	public static function getAreaDetailByCode($code)
	{
		self::getMapData();
		$map = self::$mapData;

		if ((string)$code === "999") {
			return $map["999"];
		}
		//不要调换顺序
		if (strlen($code) < 6) {
			return $map['000'] . ',' . $map[$code];
		}
		$provinceCode = substr($code, 0, 2) . '0000';
		$provinceName = $map[$provinceCode] ?? '';
		$diff_city = array_merge(self::$directAreaCodes, self::$directCityCodes);
		if (in_array($provinceCode, $diff_city, false)) {
			$provinceName = '';
		}

		$cityCode = substr($code, 0, 4) . '00';
		$cityName = $map[$cityCode] ?? '';

		$countyName = $map[$code] ?? '';
		switch ($code) {
			case $provinceCode:
				return $provinceName;
			case $cityCode:
				$data = [
					$provinceName,
					$cityName
				];
				return implode(self::REGION_CODE_SEPARATOR, array_filter($data)) ?? "";
			default:
				$data = [
					$provinceName,
					$cityName,
					$countyName
				];
				return implode(self::REGION_CODE_SEPARATOR, array_filter($data)) ?? "";
		}
	}

	/**
	 * 根据code码获取城市名称
	 *
	 * @param $code
	 * @return string
	 * @throws Exception
	 */
	public static function getNameByCode($code): string
	{
		self::getMapData();
		$map = self::$mapData;
		return $map[$code] ?? "";
	}
}
