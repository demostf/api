<?php declare(strict_types=1);

namespace Demostf\API\Demo;

class HeaderParser {
    /**
     * @param string $head string containing the demo header binary data
     * @return Header
     * @throws \InvalidArgumentException
     */
    public function parseString(string $head): Header {
        $info = @unpack(
            'A8type/Iversion/Iprotocol/A260server/A260nick/A260map/A260game/fduration/Vticks/Vframes/Vsigon',
            $head
        );
        if (!isset($info['type']) || $info['type'] !== 'HL2DEMO') {
            throw new \InvalidArgumentException('Not an HL2 demo');
        }
        return Header::fromArray($info);
    }

    /**
     * Parse demo info from a stream
     *
     * @param resource $stream
     * @return Header
     * @throws \InvalidArgumentException
     */
    public function parseStream($stream): Header {
        $head = fread($stream, 2048);
        return $this->parseString($head);
    }

    /**
     * Parse demo info from a local file
     *
     * @param string $path
     * @return Header
     * @throws \InvalidArgumentException
     */
    public function parseHeader(string $path): Header {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException('Unable to open demo: ' . $path);
        }
        $fh = fopen($path, 'rb');
        return $this->parseStream($fh);
    }
}
