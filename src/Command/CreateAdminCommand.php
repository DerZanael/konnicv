<?php

namespace App\Command;

use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Console\Question\Question;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';
    protected static $defaultDescription = 'Creates the admin user';
    
    private $em;
    private $encoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->encoder = $encoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $nb_admin = $this->em->getRepository(User::class)
            ->createQueryBuilder("usr")
            ->select("COUNT(usr.id)")
            ->getQuery()
            ->getSingleScalarResult()
        ;
        
        if((int)$nb_admin > 0) {
            $io->error("The admin user already exists");
            return Command::FAILURE;
        }

        $helper = $this->getHelper("question");
        $question_username = new Question("Admin username : ", null);
        $question_email = new Question("Admin email : ", null);
        $question_pwd = new Question("Input password : ", null);
        $question_pwd_confirm = new Question("Confirm password : ", null);

        $username = $helper->ask($input, $output, $question_username);
        $email = $helper->ask($input, $output, $question_email);
        $pwd = $helper->ask($input, $output, $question_pwd);
        $pwd_confirm = null;
        $error = false;
        while($pwd !== $pwd_confirm) {
            if($error) {
                $io->error("The passwords do not match");
            }
            $error = true;
            $pwd_confirm = $helper->ask($input, $output, $question_pwd_confirm);
        }
        if(empty($username) || empty($email) || empty($pwd)) {
            $io->error("Please provide all credentials - shutting down interface");
            return Command::FAILURE;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $hash = $this->encoder->encodePassword($user, $pwd);
        $user->setPassword($hash);
        $user->setAdmin(true);
        $this->em->persist($user);
        $this->em->flush();

        $io->success("Admin user {$username} successfuly created");

        return Command::SUCCESS;
    }
}
