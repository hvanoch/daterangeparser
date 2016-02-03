<?php

namespace Hvanoch\Component\DateIntervalParser;

class DateIntervalParser
{
    /** @var array */
    protected $aliases = array(
        'y' => 'y,yr,yrs,year,years',
        'm' => 'm,mo,mon,mos,mons,month,months',
        'w' => 'w,we,wk,week,weeks',
        'd' => 'd,dy,dys,day,days',
        'h' => 'h,hr,hrs,hour,hours',
        'i' => 'i,min,mins,minute,minutes',
        's' => 's,sec,secs,second,seconds'
    );

    /** @var array */
    protected $tokens = array();

    /** @var  \DateInterval */
    private $dateTimeInterval;

    /**
     * @param array $additionalAliases
     */
    public function __construct($additionalAliases = array())
    {
        $this->mergeAdditionalAliases($additionalAliases);

        $tokens = array();
        foreach ($this->aliases as $key => $aliasConcat) {
            $aliasList = explode(',', $aliasConcat);
            foreach ($aliasList as $alias) {
                $tokens[$alias] = $key;
            }
        }

        $this->tokens = $tokens;
    }

    private function mergeAdditionalAliases($additionalAliases)
    {
        $aliasKeys = array_keys($this->aliases);
        foreach ($additionalAliases as $key => $alias) {
            if (!in_array($key, $aliasKeys)) {
                throw new DateIntervalParseException(sprintf('Key "%" for aliases "%" is not valid', $key, $alias));
            }

            $this->aliases[$key] .= ',' . str_replace(' ', '', $alias);
        }
    }

    /**
     * @param $input
     * @return \DateInterval
     */
    public function parse($input)
    {
        $this->dateTimeInterval = new \DateInterval('P0D');

        $this->process($input);

        return $this->dateTimeInterval;
    }

    /**
     * @param string $input
     * @throws DateIntervalParseException
     */
    private function process($input)
    {
        $matches = preg_split("/[\s]+/", $input);
        foreach ($matches as $match) {
            $this->processTerm($match);

        }
    }

    /**
     * @param $input
     * @throws \Exception
     */
    private function  processTerm($input)
    {
        if (!preg_match('/(\d+)\s*([a-z]+)/i', $input, $matches)) {
            throw new DateIntervalParseException(sprintf('Unable to parse "%s"', $input));
        }
        $tokenAliased = $matches[2];
        if (!array_key_exists($tokenAliased, $this->tokens)) {
            throw new DateIntervalParseException(sprintf('To token found for "%s"', $tokenAliased));
        }

        $token = $this->tokens[$tokenAliased];
        $number = (int)$matches[1];

        /** Convert weeks to days */
        if ($token == 'w') {
            $token = 'd';
            $number = $number * 7;
        }

        $this->dateTimeInterval->{$token} += $number;
    }
}