<?php

namespace Acts\CamdramInfobaseBundle\Entity;

/**
 * ArticleTagRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleTagRepository extends \Doctrine\ORM\EntityRepository
{
    public function findSortedWithCount()
    {
        $query = $this->createQueryBuilder('t')
            ->innerJoin('t.articles', 'a')#
            ->select('t AS tag')
            ->addSelect('COUNT(a) AS num_articles')
            ->groupBy('t')
            ->orderBy('num_articles', 'DESC')
            ->getQuery();

        $res = $query->getResult();

        return $res;
    }
}