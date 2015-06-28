<?php
namespace Aura\Marshal;

use Aura\Marshal\Mock\StandardMockEntity;
use Aura\Marshal\Entity\Builder;
use Aura\Marshal\Lazy\GenericLazy;

/**
 * Test class for Entity.
 * Generated by PHPUnit on 2011-11-26 at 14:30:57.
 */
class EntityTest extends \PHPUnit_Framework_TestCase
{
    protected function getData()
    {
        return [
            'foo' => 'bar',
            'baz' => 'dim',
            'zim' => 'gir',
            'related' => new GenericLazy(new MockRelation),
        ];
    }

    public function provideMocks()
    {
        return [
            ['stdClass'],
            ['Aura\Marshal\Mock\GenericMockEntity'],
            ['Aura\Marshal\Mock\StandardMockEntity'],
            ['Aura\Marshal\Mock\PropertyTraitMockEntity'],
            ['Aura\Marshal\Mock\ArrayAccessTraitMockEntity'],
        ];
    }

    /**
     * @dataProvider provideMocks
     */
    public function testPropertyAccess($entityClass)
    {
        $builder = new MockEntityBuilder($entityClass);
        $entity = $builder->newInstance($this->getData());

        if ($entity instanceof StandardMockEntity) {
            // custom $isset is required since MockEntity has only protected properties
            $isset = \Closure::bind(
                function ($object, $prop) {
                    return isset($object->$prop);
                },
                null,
                get_class($entity)
            );

            // custom $unset is required since MockEntity has only protected properties
            $unset = \Closure::bind(
                function ($object, $prop) {
                    unset($object->$prop);
                },
                null,
                get_class($entity)
            );
        } else {
            $isset = function ($entity, $prop){ return isset($entity->$prop); };
            $unset = function ($entity, $prop){ unset($entity->$prop); };
        }

        // check set/get
        $entity->irk = 'doom';
        $this->assertSame('doom', $entity->irk);

        // check isset/unset
        $this->assertTrue($isset($entity, 'foo'));
        $unset($entity, 'foo');
        $this->assertFalse($isset($entity, 'foo'));

        $this->assertFalse($isset($entity, 'newfield'));

        $entity->newfield = 'something';
        $this->assertTrue($isset($entity, 'newfield'));

        unset($entity->newfield);
        $this->assertFalse($isset($entity, 'newfield'));

        // check relateds
        $actual = $entity->related;
        $expect = (object) ['foreign_field' => 'foreign_value'];
        $this->assertEquals($expect, $actual);
    }
}
