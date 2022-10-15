<?php

namespace backend\controllers;

use common\models\CreateEvent;
use Yii;
use common\models\Company;
use backend\models\CompanySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\db\Query;
use common\models\MessagePool;
use yii\web\Response;

/**
 * CompanyController implements the CRUD actions for Company model.
 */
class CompanyController extends Controller {

	/**
	 * {@inheritdoc}
	 */
	public function behaviors() {
		return [
			'access' => [
				'class'	 => AccessControl::className(),
				'rules'	 => [
					[
						'actions'	 => ['index', 'error', 'view', 'company-list', 'company-name-letter-filter'],
						'allow'		 => !Yii::$app->user->isGuest && Yii::$app->user->identity->canRoute('company/index'),
					],
					[
						'actions'	 => ['update', 'change-unsubscribe', 'validate-name', 'save-event'],
						'allow'		 => !Yii::$app->user->isGuest && Yii::$app->user->identity->canRoute('company/update'),
					],
					[
						'actions'	 => ['sendmail'],
						'allow'		 => !Yii::$app->user->isGuest && Yii::$app->user->identity->canRoute('company/sendmail'),
					],
					[
						'actions'	 => ['delete'],
						'allow'		 => !Yii::$app->user->isGuest && Yii::$app->user->identity->canRoute('company/delete'),
					],
					[
						'actions'	 => ['create', 'validate-name'],
						'allow'		 => !Yii::$app->user->isGuest && Yii::$app->user->identity->canRoute('company/create'),
					],
					[
						'actions'	 => ['create-montly-price-list'],
						'allow'		 => !Yii::$app->user->isGuest && Yii::$app->user->identity->canRoute('company/create-montly-price-list'),
					],
				],
			],
			'verbs'	 => [
				'class'		 => VerbFilter::className(),
				'actions'	 => [
					'delete' => ['POST'],
				],
			],
		];
	}

	/**
	 * Lists all Company models.
	 * @return mixed
	 */
	public function actionIndex() {
		$searchModel = new CompanySearch();
		$qyeryParams = Yii::$app->request->queryParams;
		if (empty($qyeryParams)) {
			$qyeryParams = Yii::$app->getSession()->get('company-queryParams');
		}
		$dataProvider = $searchModel->search($qyeryParams);

		return $this->render('index', [
				'searchModel'	 => $searchModel,
				'dataProvider'	 => $dataProvider,
		]);
	}

	public function actionCompanyNameLetterFilter() {
		$searchModel = new CompanySearch();

		$queryParams = Yii::$app->request->queryParams;
		if (empty($queryParams)) {
			$queryParams = Yii::$app->getSession()->get('company-queryParams');
		}
		$dataProvider = $searchModel->search($queryParams);

		return $this->render('index', [
				'searchModel'	 => $searchModel,
				'dataProvider'	 => $dataProvider,
		]);
	}
	
	/**
	 * Creates a new Company model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new Company();

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		}

		return $this->render('create', [
				'model' => $model,
		]);
	}

	public function actionCreateMontlyPriceList() {
		$searchModel = new CompanySearch();

		$qyeryParams	 = array_merge(Yii::$app->request->queryParams, Yii::$app->getSession()->get('company-queryParams'));
		$dataProvider	 = $searchModel->search($qyeryParams);
		$dataProvider->setPagination(false);
		$cnt			 = 0;
		$template		 = 'mail_template_montly';
		if ($qyeryParams['template']) {
			$template = $qyeryParams['template'];
		}
		foreach ($dataProvider->getModels() as $company) {
			if (!empty($company->emails) && $company->unsubscribed == Company::UNSUBSCRIBED_NO) {
				$emails = explode(';', $company->emails);
				foreach ($emails as $email) {
					$mp					 = new MessagePool();
					$mp->mail_template	 = $template;
					$mp->email			 = trim($email);
					$mp->id_company		 = $company->id;
					$mp->status			 = 'NEW';
					$mp->make_status	 = 'manual';
					$mp->source_type	 = MessagePool::SOURCE_TYPE_MONTLY_LIST;
					$mp->save();
					$cnt++;
				}
				//echo $mp->id.', ';
			}
		}
		//echo "$cnt\n\n";
		Yii::$app->session->set('flash', ['type' => 'info', 'message' => 'Email montly ' . $cnt . ' created']);
		return $this->redirect(['index']);
	}

	/**
	 * Updates an existing Company model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id) {
		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		}
        $createEventModel = new CreateEvent();
        $createEventModel->type = CreateEvent::TYPE_COMPANY;
        $createEventModel->model_key = $model->id;
        $event = CreateEvent::findAll(['type'=> CreateEvent::TYPE_COMPANY, 'model_key' => $model->id]);
		return $this->render('update', [
            'model' => $model,
            'createEventModel' => $createEventModel,
            'event' => $event

		]);
	}

	public function actionView($id) {
		$model = $this->findModel($id);

		return $this->render('view', [
				'model' => $model,
		]);
	}

	/**
	 * Deletes an existing Company model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionDelete($id) {
		$this->findModel($id)->delete();

		return $this->redirect(['index']);
	}

	/**
	 * Finds the Company model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Company the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = Company::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
	}

	public function actionCompanyList($q = null, $id = null) {
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		$out						 = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($q)) {
			$query			 = new Query;
			$query->select('id, name AS text')
				->from('company')
				->where(['like', 'name', $q])
				->limit(20);
			$command		 = $query->createCommand();
			$data			 = $command->queryAll();
			$out['results']	 = array_values($data);
		} elseif ($id > 0) {
			$out['results'] = ['id' => $id, 'text' => Company::find($id)->name];
		}
		return $out;
	}

	public function actionChangeUnsubscribe() {
		$ret = [
			'success' => false
		];
		Yii::$app->response->format	 = Response::FORMAT_JSON;
		$id							 = Yii::$app->request->post('id');
		if (Yii::$app->request->isAjax && $id) {
			$company = Company::findOne($id);
			if ($company) {
				$company->unsubscribed = $company->unsubscribed == Company::UNSUBSCRIBED_NO ? Company::UNSUBSCRIBED_YES : Company::UNSUBSCRIBED_NO;
				$ret = [
					'success'		 => $company->save(false, ['unsubscribed']),
					'unsubscribed'	 => $company->unsubscribed,
					'id'			 => $company->id
				];
			}
		}
		return $ret;
	}

	public function actionSendmail() {
		if (Yii::$app->request->get('id') && Yii::$app->request->get('template')) {
			$email = Yii::$app->request->get('email');
			if ($email) {
				$mp					 = new MessagePool();
				$mp->email			 = $email;
				$mp->id_company		 = Yii::$app->request->get('id');
				$mp->status			 = MessagePool::STATUS_NEW;
				$mp->make_status	 = MessagePool::MAKE_STATUS_MANUAL;
				$mp->mail_template	 = Yii::$app->request->get('template');
				$mp->source_type	 = MessagePool::SOURCE_TYPE_NOTIFICATION;
				if ($mp->save()) {
					Yii::$app->session->set('flash', ['type' => 'success', 'message' => 'Message created']);
				} else {
					Yii::$app->session->set('flash', ['type' => 'danger', 'message' => 'Fail action. ' . json_encode($mp->errors)]);
				}
			}
		}
		/*
		  $page = 0;
		  if (Yii::$app->getSession()->has('ships-queryPage')) {
		  $page = Yii::$app->getSession()->get('ships-queryPage', 0);
		  } */
		return $this->redirect(['index'/* , 'page' => $page */]);
	}

	public function actionValidateName() {
		Yii::$app->response->format = Response::FORMAT_JSON;
		$company_name = Yii::$app->request->post('company_name');
		$keywords = array_unique(str_word_count($company_name, 1, '0..9-_\.\''));
		if (empty($keywords)) {
			return ['valid' => true];
		}
		$where = ['and'];
		foreach ($keywords as $keyword) {
			$keyword = mb_strtolower(strtr($keyword, ['%' => '\%', '_' => '\_']));
			$where[] = ['like', 'LOWER(name)', $keyword];
		}
		$duplicate_company = Company::find()->select('name')->where($where)->asArray()->all();
		return ['valid' => empty($duplicate_company), 'duplicate_company' => $duplicate_company];
	}

    public function  actionSaveEvent() {
        $model = new CreateEvent();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->model_key]);
        }
    }

}