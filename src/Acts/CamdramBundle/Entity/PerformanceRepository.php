<?php

namespace Acts\CamdramBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\Query\Expr;

/**
 * PerformanceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PerformanceRepository extends EntityRepository
{
    /**
     * findInDateRange
     *
     * Find all authorized performances between two dates, joined to the
     * corresponding show.
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array
     */
    public function findInDateRange(\DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.show', 's')
            ->leftjoin('s.venue', 'v')
            ->addSelect('s')
            ->addSelect('v')
            ->where('s.authorised_by is not null')
            ->andWhere('s.entered = true')
            ->andWhere('p.start_date <= :end')
            ->andWhere('p.end_date >= :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
        ;
        return $qb->getQuery()->getResult();
    }

    public function getNumberInDateRange(\DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.start_date <= :end')
            ->andWhere('p.end_date >= :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        $result = $qb->getQuery()->getResult();
        $count = 0;
        $max_end = clone $end;
        $max_end->modify('-1 day');
        foreach ($result as $p) {
            $start_at = $p->getStartDate();
            $end_at = $p->getEndDate();
            if ($start_at < $start) $start_at = $start;
            if ($end_at > $end) $end_at = $max_end;

            $count += $end_at->diff($start_at)->d + 1;
            if ($p->getExcludeDate() && $p->getExcludeDate()->format('u') > 0
                && $p->getExcludeDate() > $start_at && $p->getExcludeDate() < $end_at) $count--;
        }

        return $count;
    }

    public function getBySociety(Society $society, \DateTime $from = null, \DateTime $to = null)
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.show', 's')
            ->leftjoin('s.venue', 'v')
            ->addSelect('s')
            ->addSelect('v')
            ->where('s.authorised_by is not null')
            ->andWhere('s.entered = true')            
            ->andWhere('s.society = :society');
            
        if($from){
            $query = $query->andWhere('p.end_date > :from')->setParameter('from', $from);
        }
        
        if($to){
            $query = $query->andWhere('p.end_date <= :to')->setParameter('to', $to);
        }

            
        $query = $query->orderBy('p.start_date', 'ASC')
            ->setParameter('society', $society)
            ->getQuery();
        return $query->getResult();
    }

    public function getByVenue(Venue $venue, \DateTime $from = null, \DateTime $to = null)
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.show', 's')
            ->join('s.venue', 'v')
            ->addSelect('s')
            ->addSelect('v')
            ->where('s.authorised_by is not null')
            ->andWhere('s.entered = true');
            
        if($from){
            $query = $query->andWhere('p.end_date > :from')->setParameter('from', $from);
        }
        
        if($to){
            $query = $query->andWhere('p.end_date <= :to')->setParameter('to', $to);
        }
        
        $query = $query->andWhere('s.venue = :venue')
            ->orderBy('p.start_date', 'ASC')
            ->setParameter('venue', $venue)
            ->getQuery();
        return $query->getResult();
    }
}
