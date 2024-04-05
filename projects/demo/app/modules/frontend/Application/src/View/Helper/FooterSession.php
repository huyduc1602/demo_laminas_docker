<?php
namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * This view helper class displays a menu bar.
 */
class FooterSession extends AbstractHelper
{
    /**
     * EntityManager
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_entityManager = null;
    /**
     * Constructor.
     * @param \Doctrine\ORM\EntityManager $items.
     */
    public function __construct( $entityManager = null)
    {
        $this->_entityManager = $entityManager;
    }

    /**
     * Renders the menu.
     * @return string HTML code of the menu.
     */
    public function __invoke()
    {
        $data['constant'] = $this->_entityManager
            ->getRepository('\Models\Entities\Constant')
            ->findOneBy(['constant_code' => 'tml_footer']);
        return $this->getView()->render('application/layout/footer', $data);
    }
}
