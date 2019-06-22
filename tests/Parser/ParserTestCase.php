<?php
/**
 * This file is part of compiler package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Tests\Parser;

use Phplrt\Io\File;
use Phplrt\Ast\Dumper\HoaDumper;
use PHPUnit\Framework\Exception;
use Phplrt\Contracts\Io\Readable;
use Phplrt\Contracts\Parser\ParserInterface;
use Phplrt\Io\Exception\NotReadableException;

/**
 * Class PP2ParserTestCase
 */
class ParserTestCase extends TestCase
{
    /**
     * @return array
     * @throws NotReadableException
     */
    public function grammars(): array
    {
        $sources = [
            'PP2'  => [new PP2Llk(), __DIR__ . '/pp2/*.pp2'],
            'JSON' => [new JsonLlk(), __DIR__ . '/json/*.json'],
            'SDL'  => [new SdlLlk(), __DIR__ . '/sdl/*.graphqls'],
        ];

        $result = [];

        foreach ($sources as $name => [$parser, $directory]) {
            foreach (\glob($directory) as $file) {
                $result[$name . ':' . \basename($file)] = [$parser, File::fromPathname($file)];
            }
        }

        return $result;
    }

    /**
     * @dataProvider grammars
     *
     * @param ParserInterface $parser
     * @param Readable $file
     * @throws Exception
     */
    public function testCompareAst(ParserInterface $parser, Readable $file): void
    {
        $ast = $parser->parse($file);

        $actual = (new HoaDumper())->dump($ast);

        $haystack = \trim(\file_get_contents($file->getPathname() . '.txt'));
        $haystack = \str_replace("\r", '', $haystack);

        $this->assertEquals($haystack, \str_replace("\r", '', $actual));
    }
}
