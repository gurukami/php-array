<?php

namespace Gurukami\Helpers;

class ArraysTest extends \TestSuite
{
    /**
     * @covers \Gurukami\Helpers\Arrays::exists
     * @group helpers
     */
    public function testExists()
    {
        $array = [
            '' => 'empty',
            0 => '0',
            '1' => '1',
            '@\#$%^&*()\'8:.,~/"{}' => 'spec-value',
            'inclBr[]' => 'inclBr',
            'brokenBr[' => 'brokenBr',
            2 => [
                '@\#$%^&*()\'8:.,~/"{}' => 'spec-value',
                'inclBr[]' => 'inclBr',
                'brokenBr[' => 'brokenBr',
                true => '1',
                false => '0',
                null => 'null',
                'k0' => '0',
                'k1' => '1',
                'k2' => [
                    '0'
                ]
            ],
            3 => null
        ];

        $emptyArray = [];
        $nullArray = ['' => null];
        $string = 'string';

        // Empty key, '' == null
        $this->assertTrue(Arrays::exists('', $nullArray));
        $this->assertTrue(Arrays::exists('', $array));
        $this->assertTrue(Arrays::exists('[""]', $array));
        $this->assertTrue(Arrays::exists('2[""]', $array));
        $this->assertTrue(Arrays::exists('[2][""]', $array));
        $this->assertTrue(Arrays::exists('["2"]', $array));
        $this->assertFalse(Arrays::exists('"2"', $array));
        $this->assertFalse(Arrays::exists('[]', $array));
        $this->assertFalse(Arrays::exists('2[]', $array));
        $this->assertFalse(Arrays::exists('[2][]', $array));
        $this->assertFalse(Arrays::exists('', $emptyArray));
        $this->assertFalse(Arrays::exists('[]', $emptyArray));
        $this->assertFalse(Arrays::exists('', $string));

        // Null key, null == ''
        $this->assertTrue(Arrays::exists(null, $array));
        $this->assertFalse(Arrays::exists(null, $emptyArray));
        $this->assertFalse(Arrays::exists(null, $string));

        // Boolean & Index key
        // true
        $this->assertTrue(Arrays::exists(true, $array));
        $this->assertTrue(Arrays::exists(1, $array));
        $this->assertTrue(Arrays::exists('1', $array));
        $this->assertTrue(Arrays::exists('2[1]', $array));
        $this->assertTrue(Arrays::exists('[2][1]', $array));
        $this->assertTrue(Arrays::exists('[2]1', $array));
        $this->assertFalse(Arrays::exists(true, $emptyArray));
        $this->assertFalse(Arrays::exists(true, $string));
        // false
        $this->assertTrue(Arrays::exists(false, $array));
        $this->assertTrue(Arrays::exists(0, $array));
        $this->assertTrue(Arrays::exists('0', $array));
        $this->assertTrue(Arrays::exists('2[0]', $array));
        $this->assertTrue(Arrays::exists('[2][0]', $array));
        $this->assertTrue(Arrays::exists('[2]0', $array));
        $this->assertFalse(Arrays::exists(false, $emptyArray));
        $this->assertFalse(Arrays::exists(false, $string));

        // Special chars
        $this->assertTrue(Arrays::exists('@\#$%^&*()\'8:.,~/"{}', $array));
        $this->assertTrue(Arrays::exists('[@\#$%^&*()\'8:.,~/"{}]', $array));
        $this->assertTrue(Arrays::exists('2[@\#$%^&*()\'8:.,~/"{}]', $array));
        $this->assertTrue(Arrays::exists('[2][@\#$%^&*()\'8:.,~/"{}]', $array));
        $this->assertTrue(Arrays::exists('[2]@\#$%^&*()\'8:.,~/"{}', $array));
        $this->assertFalse(Arrays::exists('@\#$%^&*()\'8:.,~/"{}', $emptyArray));
        $this->assertFalse(Arrays::exists('@\#$%^&*()\'8:.,~/"{}', $string));

        // Include brackets
        $this->assertTrue(Arrays::exists('[inclBr[]]', $array));
        $this->assertTrue(Arrays::exists('["inclBr[]"]', $array));
        $this->assertTrue(Arrays::exists('[\'inclBr[]\']', $array));
        $this->assertTrue(Arrays::exists('2[inclBr[]]', $array));
        $this->assertTrue(Arrays::exists('[2][inclBr[]]', $array));
        $this->assertFalse(Arrays::exists('inclBr[]', $array));
        $this->assertFalse(Arrays::exists('[2]inclBr[]', $array));

        // Broken brackets
        $this->assertTrue(Arrays::exists('["brokenBr["]', $array));
        $this->assertTrue(Arrays::exists('[\'brokenBr[\']', $array));
        $this->assertTrue(Arrays::exists('2["brokenBr["]', $array));
        $this->assertTrue(Arrays::exists('[2]["brokenBr["]', $array));
        $this->assertFalse(Arrays::exists('[2]"brokenBr["', $array));
        $this->assertFalse(Arrays::exists('[brokenBr[]', $array));
        $this->assertFalse(Arrays::exists('brokenBr[', $array));
        $this->assertFalse(Arrays::exists('brokenBr]', $array));
        $this->assertFalse(Arrays::exists('[brokenBr', $array));
        $this->assertFalse(Arrays::exists(']brokenBr', $array));

        // Execute code
        Arrays::exists('\'.die(-1).\'".die(-1)."', $emptyArray);

        // Other types
        $obj = self::getObj();
        $this->assertFalse(Arrays::exists('unknown', $obj));

        $int = self::getInt();
        $this->assertFalse(Arrays::exists('unknown', $int));

        $float = self::getFloat();
        $this->assertFalse(Arrays::exists('unknown', $float));

        $res = self::getRes();
        $this->assertFalse(Arrays::exists('unknown', $res));

        // Broken quote
        $this->assertFalse(Arrays::exists('["unknown\']', $array));
        $this->assertFalse(Arrays::exists('"unknown\'', $array));
        $this->assertFalse(Arrays::exists('"unknown\'"""""\'\'\'\'', $array));
    }

    /**
     * @covers \Gurukami\Helpers\Arrays::save
     * @group helpers
     */
    public function testSave()
    {
        $string = 'string';

        // Empty key, '' == null
        $array = [];
        $this->assertTrue(Arrays::save('', $array, 'empty'));
        $this->assertEquals('empty', $array['']);

        $array = [];
        $this->assertTrue(Arrays::save('[""]', $array, 'empty'));
        $this->assertEquals('empty', $array['']);

        $array = [];
        $this->assertTrue(Arrays::save('2[""]', $array, 'null'));
        $this->assertEquals('null', $array[2]['']);

        $array = [];
        $this->assertTrue(Arrays::save('[2][""]', $array, 'null'));
        $this->assertEquals('null', $array[2]['']);

        $this->assertFalse(Arrays::save('', $string, 'empty'));
        $this->assertEquals('string', $string);

        // Null key, null == ''
        $array = [];
        $this->assertTrue(Arrays::save(null, $array, 'empty'));
        $this->assertEquals('empty', $array['']);

        $this->assertFalse(Arrays::save(null, $string, 'empty'));
        $this->assertEquals('string', $string);

        // Boolean & Index key
        // true
        $array = [];
        $this->assertTrue(Arrays::save(true, $array, '1'));
        $this->assertEquals('1', $array[1]);

        $array = [];
        $this->assertTrue(Arrays::save(1, $array, '1'));
        $this->assertEquals('1', $array[1]);

        $array = [];
        $this->assertTrue(Arrays::save('1', $array, '1'));
        $this->assertEquals('1', $array[1]);

        $array = [];
        $this->assertTrue(Arrays::save('2[1]', $array, '1'));
        $this->assertEquals('1', $array[2][1]);

        $array = [];
        $this->assertTrue(Arrays::save('[2][1]', $array, '1'));
        $this->assertEquals('1', $array[2][1]);

        $array = [];
        $this->assertTrue(Arrays::save('[2]1', $array, '1'));
        $this->assertEquals('1', $array[2][1]);

        $this->assertFalse(Arrays::save(true, $string, '1'));
        $this->assertEquals('string', $string);

        // false
        $array = [];
        $this->assertTrue(Arrays::save(false, $array, '0'));
        $this->assertEquals('0', $array[0]);

        $array = [];
        $this->assertTrue(Arrays::save(0, $array, '0'));
        $this->assertEquals('0', $array[0]);

        $array = [];
        $this->assertTrue(Arrays::save('0', $array, '0'));
        $this->assertEquals('0', $array[0]);

        $array = [];
        $this->assertTrue(Arrays::save('2[0]', $array, '0'));
        $this->assertEquals('0', $array[2][0]);

        $array = [];
        $this->assertTrue(Arrays::save('[2][0]', $array, '0'));
        $this->assertEquals('0', $array[2][0]);

        $array = [];
        $this->assertTrue(Arrays::save('[2]0', $array, '0'));
        $this->assertEquals('0', $array[2][0]);

        $this->assertFalse(Arrays::save(false, $string, '0'));
        $this->assertEquals('string', $string);

        // Special chars
        $array = [];
        $this->assertTrue(Arrays::save('@\#$%^&*()\'8:.,~/"{}', $array, 'spec-value'));
        $this->assertEquals('spec-value', $array['@\#$%^&*()\'8:.,~/"{}']);

        $array = [];
        $this->assertTrue(Arrays::save('[@\#$%^&*()\'8:.,~/"{}]', $array, 'spec-value'));
        $this->assertEquals('spec-value', $array['@\#$%^&*()\'8:.,~/"{}']);

        $array = [];
        $this->assertTrue(Arrays::save('2[@\#$%^&*()\'8:.,~/"{}]', $array, 'spec-value'));
        $this->assertEquals('spec-value', $array[2]['@\#$%^&*()\'8:.,~/"{}']);

        $array = [];
        $this->assertTrue(Arrays::save('[2][@\#$%^&*()\'8:.,~/"{}]', $array, 'spec-value'));
        $this->assertEquals('spec-value', $array[2]['@\#$%^&*()\'8:.,~/"{}']);

        $array = [];
        $this->assertTrue(Arrays::save('[2]@\#$%^&*()\'8:.,~/"{}', $array, 'spec-value'));
        $this->assertEquals('spec-value', $array[2]['@\#$%^&*()\'8:.,~/"{}']);

        $this->assertFalse(Arrays::save('@\#$%^&*()\'8:.,~/"{}', $string, 'spec-value'));
        $this->assertEquals('string', $string);

        // Include brackets
        $array = [];
        $this->assertTrue(Arrays::save('[inclBr[]]', $array, 'inclBr'));
        $this->assertEquals('inclBr', $array['inclBr[]']);

        $array = [];
        $this->assertTrue(Arrays::save('2[inclBr[]]', $array, 'inclBr'));
        $this->assertEquals('inclBr', $array[2]['inclBr[]']);

        $array = [];
        $this->assertTrue(Arrays::save('[2][inclBr[]]', $array, 'inclBr'));
        $this->assertEquals('inclBr', $array[2]['inclBr[]']);

        $array = [];
        $this->assertTrue(Arrays::save('inclBr[]', $array, 'inclBr'));
        $this->assertEquals('inclBr', $array['inclBr'][0]);

        $array = [];
        $this->assertTrue(Arrays::save('[2]inclBr[]', $array, 'inclBr'));
        $this->assertEquals('inclBr', $array[2]['inclBr'][0]);

        // Broken brackets
        $array = [];
        $this->assertFalse(Arrays::save('[brokenBr[]', $array, 'brokenBr'));
        $this->assertEquals([], $array);

        $array = [];
        $this->assertTrue(Arrays::save('["brokenBr["]', $array, 'brokenBr'));
        $this->assertEquals('brokenBr', $array['brokenBr[']);

        $array = [];
        $this->assertFalse(Arrays::save('brokenBr[', $array, 'brokenBr'));
        $this->assertEquals([], $array);

        // Append
        $array = [];
        $this->assertTrue(Arrays::save('[]', $array, 'append'));
        $this->assertEquals('append', $array[0]);

        $array = [];
        $this->assertTrue(Arrays::save('append[]', $array, 'append'));
        $this->assertEquals('append', $array['append'][0]);

        $array = [];
        $this->assertFalse(Arrays::save('append[][][][]', $array, 'append'));
        $this->assertEquals([], $array);

        $array = [];
        $this->assertTrue(Arrays::save('append[sub][]', $array, 'append'));
        $this->assertEquals('append', $array['append']['sub'][0]);

        // Execute code
        $array = [];
        Arrays::save('\'.die(-1).\'".die(-1)."', $array, '\'.die(-1).\'".die(-1)."');

        // Other types
        $obj = self::getObj();
        $this->assertFalse(Arrays::save('unknown', $obj, 'unknown'));

        $int = self::getInt();
        $this->assertFalse(Arrays::save('unknown', $int, 'unknown'));

        $float = self::getFloat();
        $this->assertFalse(Arrays::save('unknown', $float, 'unknown'));

        $res = self::getRes();
        $this->assertFalse(Arrays::save('unknown', $res, 'unknown'));

        // Broken quote
        $array = [];
        $this->assertTrue(Arrays::save('["unknown\']', $array, 'unknown'));
        $this->assertEquals('unknown', $array['"unknown\'']);

        $array = [];
        $this->assertTrue(Arrays::save('"unknown\'', $array, 'unknown'));
        $this->assertEquals('unknown', $array['"unknown\'']);

    }

    /**
     * @covers \Gurukami\Helpers\Arrays::delete
     * @group helpers
     */
    public function testDelete()
    {
        $array = [
            '' => 'empty',
            0 => '0',
            '1' => '1',
            '@\#$%^&*()\'8:.,~/"{}' => 'spec-value',
            'inclBr[]' => 'inclBr',
            'brokenBr[' => 'brokenBr',
            2 => [
                '@\#$%^&*()\'8:.,~/"{}' => 'spec-value',
                'inclBr[]' => 'inclBr',
                'brokenBr[' => 'brokenBr',
                true => '1',
                false => '0',
                null => 'null',
                'k0' => '0',
                'k1' => '1',
                'k2' => [
                    '0'
                ]
            ]
        ];

        $emptyArray = [];
        $string = 'string';

        // Empty key, '' == null
        $copyArray = $array;
        $this->assertTrue(Arrays::delete('', $copyArray));
        $this->assertFalse(isset($copyArray['']));

        $copyArray = $array;
        $this->assertFalse(Arrays::delete('[]', $copyArray));
        $this->assertTrue(isset($copyArray['']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[""]', $copyArray));
        $this->assertFalse(isset($copyArray['']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('2[""]', $copyArray));
        $this->assertFalse(isset($copyArray[2]['']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[2][""]', $copyArray));
        $this->assertFalse(isset($copyArray[2]['']));

        $this->assertFalse(Arrays::delete('', $string));
        $this->assertEquals('string', $string);

        // Null key, null == ''
        $copyArray = $array;
        $this->assertTrue(Arrays::delete(null, $copyArray));
        $this->assertFalse(isset($copyArray[null]));

        $this->assertFalse(Arrays::delete(null, $string));
        $this->assertEquals('string', $string);

        // Boolean & Index key
        // true
        $copyArray = $array;
        $this->assertTrue(Arrays::delete(true, $copyArray));
        $this->assertFalse(isset($copyArray[true]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete(1, $copyArray));
        $this->assertFalse(isset($copyArray[1]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('1', $copyArray));
        $this->assertFalse(isset($copyArray[1]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('2[1]', $copyArray));
        $this->assertFalse(isset($copyArray[2][1]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[2][1]', $copyArray));
        $this->assertFalse(isset($copyArray[2][1]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[2]1', $copyArray));
        $this->assertFalse(isset($copyArray[2][1]));

        $this->assertFalse(Arrays::delete(true, $string));
        $this->assertEquals('string', $string);

        // false
        $copyArray = $array;
        $this->assertTrue(Arrays::delete(false, $copyArray));
        $this->assertFalse(isset($copyArray[false]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete(0, $copyArray));
        $this->assertFalse(isset($copyArray[0]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('0', $copyArray));
        $this->assertFalse(isset($copyArray[0]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('2[0]', $copyArray));
        $this->assertFalse(isset($copyArray[2][0]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[2][0]', $copyArray));
        $this->assertFalse(isset($copyArray[2][0]));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[2]0', $copyArray));
        $this->assertFalse(isset($copyArray[2][0]));

        $this->assertFalse(Arrays::delete(false, $string));
        $this->assertEquals('string', $string);

        // Special chars
        $copyArray = $array;
        $this->assertTrue(Arrays::delete('@\#$%^&*()\'8:.,~/"{}', $copyArray));
        $this->assertFalse(isset($copyArray['@\#$%^&*()\'8:.,~/"{}']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[@\#$%^&*()\'8:.,~/"{}]', $copyArray));
        $this->assertFalse(isset($copyArray['@\#$%^&*()\'8:.,~/"{}']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('2[@\#$%^&*()\'8:.,~/"{}]', $copyArray));
        $this->assertFalse(isset($copyArray[2]['@\#$%^&*()\'8:.,~/"{}']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[2][@\#$%^&*()\'8:.,~/"{}]', $copyArray));
        $this->assertFalse(isset($copyArray[2]['@\#$%^&*()\'8:.,~/"{}']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[2]@\#$%^&*()\'8:.,~/"{}', $copyArray));
        $this->assertFalse(isset($copyArray[2]['@\#$%^&*()\'8:.,~/"{}']));

        $this->assertFalse(Arrays::delete('@\#$%^&*()\'8:.,~/"{}', $string));
        $this->assertEquals('string', $string);

        // Include brackets
        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[inclBr[]]', $copyArray));
        $this->assertFalse(isset($copyArray['inclBr[]']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('2[inclBr[]]', $copyArray));
        $this->assertFalse(isset($copyArray[2]['inclBr[]']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('[2][inclBr[]]', $copyArray));
        $this->assertFalse(isset($copyArray[2]['inclBr[]']));

        $copyArray = $array;
        $this->assertFalse(Arrays::delete('inclBr[]', $copyArray));
        $this->assertTrue(isset($copyArray['inclBr[]']));

        $copyArray = $array;
        $this->assertFalse(Arrays::delete('[2]inclBr[]', $copyArray));
        $this->assertTrue(isset($copyArray[2]['inclBr[]']));

        // Broken brackets
        $copyArray = $array;
        $this->assertFalse(Arrays::delete('[brokenBr[]', $copyArray));
        $this->assertTrue(isset($copyArray['brokenBr[']));

        $copyArray = $array;
        $this->assertTrue(Arrays::delete('["brokenBr["]', $copyArray));
        $this->assertFalse(isset($copyArray['brokenBr[']));

        $copyArray = $array;
        $this->assertFalse(Arrays::delete('brokenBr[', $copyArray));
        $this->assertTrue(isset($copyArray['brokenBr[']));

        // Execute code
        Arrays::delete('\'.die(-1).\'".die(-1)."', $emptyArray);

        // Other types
        $obj = self::getObj();
        $this->assertFalse(Arrays::delete('unknown', $obj));

        $int = self::getInt();
        $this->assertFalse(Arrays::delete('unknown', $int));

        $float = self::getFloat();
        $this->assertFalse(Arrays::delete('unknown', $float));

        $res = self::getRes();
        $this->assertFalse(Arrays::delete('unknown', $res));

        // Broken quote
        $this->assertFalse(Arrays::delete('["unknown\']', $array));
        $this->assertFalse(Arrays::delete('"unknown\'', $array));
    }

    /**
     * @covers \Gurukami\Helpers\Arrays::get
     * @group helpers
     */
    public function testGet()
    {
        $array = [
            '' => 'empty',
            0 => '0',
            '1' => '1',
            '@\#$%^&*()\'8:.,~/"{}' => 'spec-value',
            'inclBr[]' => 'inclBr',
            'brokenBr[' => 'brokenBr',
            2 => [
                '@\#$%^&*()\'8:.,~/"{}' => 'spec-value',
                'inclBr[]' => 'inclBr',
                'brokenBr[' => 'brokenBr',
                true => '1',
                false => '0',
                null => 'null',
                'k0' => '0',
                'k1' => '1',
                'k2' => [
                    '0'
                ]
            ],
            '"quote\'' => 'quote'
        ];

        $emptyArray = [];
        $string = 'string';

        // Empty key, '' == null
        $this->assertEquals('empty', Arrays::get('', $array));
        $this->assertEquals('empty', Arrays::get('[""]', $array));
        $this->assertEquals('null', Arrays::get('2[""]', $array));
        $this->assertEquals('null', Arrays::get('[2][""]', $array));
        $this->assertEquals($array[2], Arrays::get('["2"]', $array));
        $this->assertEquals('default', Arrays::get('"2"', $array, 'default'));
        $this->assertEquals('default', Arrays::get('[]', $array, 'default'));
        $this->assertEquals('default', Arrays::get('2[]', $array, 'default'));
        $this->assertEquals('default', Arrays::get('[2][]', $array, 'default'));
        $this->assertEquals('default', Arrays::get('', $emptyArray, 'default'));
        $this->assertEquals('default', Arrays::get('[]', $emptyArray, 'default'));
        $this->assertEquals('default', Arrays::get('', $string, 'default'));

        // Null key, null == ''
        $this->assertEquals('empty', Arrays::get(null, $array));
        $this->assertEquals('default', Arrays::get(null, $emptyArray, 'default'));
        $this->assertEquals('default', Arrays::get(null, $string, 'default'));

        // Boolean & Index key
        // true
        $this->assertEquals('1', Arrays::get(true, $array));
        $this->assertEquals('1', Arrays::get(1, $array));
        $this->assertEquals('1', Arrays::get('1', $array));
        $this->assertEquals('1', Arrays::get('2[1]', $array));
        $this->assertEquals('1', Arrays::get('[2][1]', $array));
        $this->assertEquals('1', Arrays::get('[2]1', $array));
        $this->assertEquals('default', Arrays::get(true, $emptyArray, 'default'));
        $this->assertEquals('default', Arrays::get(true, $string, 'default'));
        // false
        $this->assertEquals('0', Arrays::get(false, $array));
        $this->assertEquals('0', Arrays::get(0, $array));
        $this->assertEquals('0', Arrays::get('0', $array));
        $this->assertEquals('0', Arrays::get('2[0]', $array));
        $this->assertEquals('0', Arrays::get('[2][0]', $array));
        $this->assertEquals('0', Arrays::get('[2]0', $array));
        $this->assertEquals('default', Arrays::get(false, $emptyArray, 'default'));
        $this->assertEquals('default', Arrays::get(false, $string, 'default'));


        // Special chars
        $this->assertEquals('spec-value', Arrays::get('@\#$%^&*()\'8:.,~/"{}', $array));
        $this->assertEquals('spec-value', Arrays::get('[@\#$%^&*()\'8:.,~/"{}]', $array));
        $this->assertEquals('spec-value', Arrays::get('2[@\#$%^&*()\'8:.,~/"{}]', $array));
        $this->assertEquals('spec-value', Arrays::get('[2][@\#$%^&*()\'8:.,~/"{}]', $array));
        $this->assertEquals('spec-value', Arrays::get('[2]@\#$%^&*()\'8:.,~/"{}', $array));
        $this->assertEquals('default', Arrays::get('@\#$%^&*()\'8:.,~/"{}', $emptyArray, 'default'));
        $this->assertEquals('default', Arrays::get('@\#$%^&*()\'8:.,~/"{}', $string, 'default'));

        // Include brackets
        $this->assertEquals('inclBr', Arrays::get('[inclBr[]]', $array));
        $this->assertEquals('inclBr', Arrays::get('["inclBr[]"]', $array));
        $this->assertEquals('inclBr', Arrays::get('[\'inclBr[]\']', $array));
        $this->assertEquals('inclBr', Arrays::get('2[inclBr[]]', $array));
        $this->assertEquals('inclBr', Arrays::get('[2][inclBr[]]', $array));
        $this->assertEquals('default', Arrays::get('inclBr[]', $array, 'default'));
        $this->assertEquals('default', Arrays::get('[2]inclBr[]', $array, 'default'));

        // Broken brackets
        $this->assertEquals('brokenBr', Arrays::get('["brokenBr["]', $array));
        $this->assertEquals('brokenBr', Arrays::get('2["brokenBr["]', $array));
        $this->assertEquals('brokenBr', Arrays::get('[2]["brokenBr["]', $array));
        $this->assertEquals('default', Arrays::get('[2]"brokenBr["', $array, 'default'));
        $this->assertEquals('default', Arrays::get('[brokenBr[]', $array, 'default'));
        $this->assertEquals('default', Arrays::get('brokenBr[', $array, 'default'));
        $this->assertEquals('default', Arrays::get('brokenBr]', $array, 'default'));
        $this->assertEquals('default', Arrays::get('[brokenBr', $array, 'default'));
        $this->assertEquals('default', Arrays::get(']brokenBr', $array, 'default'));

        // Execute code
        Arrays::get('\'.die(-1).\'".die(-1)."', $emptyArray);

        // Ignore string == false
        $this->assertEquals('e', Arrays::get('[""][0]', $array, null, false));
        $this->assertEquals('p', Arrays::get('[""][2]', $array, null, false));

        // Other types
        $obj = self::getObj();
        $this->assertEquals('default', Arrays::get('unknown', $obj, 'default'));

        $int = self::getInt();
        $this->assertEquals('default', Arrays::get('unknown', $int, 'default'));

        $float = self::getFloat();
        $this->assertEquals('default', Arrays::get('unknown', $float, 'default'));

        $res = self::getRes();
        $this->assertEquals('default', Arrays::get('unknown', $res, 'default'));

        // Broken quote
        $this->assertEquals('quote', Arrays::get('["quote\']', $array));
        $this->assertEquals('quote', Arrays::get('"quote\'', $array));
    }
}
