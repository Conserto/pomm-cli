<?php
/*
 * This file is part of Pomm's Cli package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Cli\Test\Fixture;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Client\Client;

class StructureFixtureClient extends Client
{
    public function getClientType()
    {
        return 'fixture';
    }

    public function getClientIdentifier()
    {
        return get_class($this);
    }

    public function initialize(Session $session)
    {
        parent::initialize($session);
        $sql = [
            'begin',
            'create schema pomm_test',
            'create table pomm_test.alpha(alpha_one serial primary key, alpha_two varchar not null, alpha_three timestamp not null default now())',
            'create table pomm_test.beta(beta_one serial, beta_two int4, beta_three xml not null, primary key(beta_one, beta_two), unique(beta_one))',
            'create table pomm_test.charly(charly_one char(2) unique, charly_two point)',
            'create view pomm_test.dingo as select * from pomm_test.charly',
            'comment on schema pomm_test is $c$This is a test schema.$c$',
            'comment on table pomm_test.beta is $c$This is the beta comment.$c$',
            'comment on column pomm_test.beta.beta_one is $c$This is the beta.one comment.$c$',
            'commit',
            ];
        $this->executeSql(join(';', $sql));
    }

    public function shutdown()
    {
        $sql = 'drop schema pomm_test cascade';
        $this->executeSql($sql);
    }

    protected function executeSql($sql)
    {
        $this
            ->getSession()
            ->getConnection()
            ->executeAnonymousQuery($sql)
            ;
    }
}
