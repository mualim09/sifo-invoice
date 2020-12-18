<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invoice extends CI_Controller {

	public function __construct() {
		parent::__construct();
		if(!$this->session->userdata('logged_in')) {redirect('login','refresh');}//user harus login
		$this->load->model('Model_ci');

	}

	public function index() {
		$data['judul']="Generate Invoice";
		$id_user=$this->session->userdata('id_user');
		$no_urut=$this->autoid();
		$bulan=date('m');
		$tahun=date('Y');
		$roman=$this->numberToRoman($bulan);
		$data['no_urut']=$no_urut;
		$data['invoice']=$this->Model_ci->get_where_order('tb_invoice','no_urut','DESC',array('id_user'=>$id_user));
		$data['no_invoice']=$no_urut."/DIV-IT/".$roman."/".$tahun;
		$this->template->display('create_invoice',$data);
	}

	public function addData() {
		$data=array(
			'no_urut'=>$this->input->post('no_urut',true),
			'no_invoice'=>$this->input->post('no_invoice',true),
			'kode_project'=>$this->input->post('kode_project',true),
			'id_user'=>$this->session->userdata('id_user'),
			'payment_type'=>$this->input->post('payment_type',true),
			'due_date'=>$this->input->post('due_date',true),
			'date'=>$this->input->post('date',true),
			'kepada'=>$this->input->post('kepada',true),
			'note'=>$this->input->post('note',true),
			'is_paid'=>$this->input->post('is_paid',true)
		);
		$this->Model_ci->insert('tb_invoice',$data);
		$this->session->set_flashdata('alert','Invoice Berhasil Tersimpan!');
		redirect('invoice');
	}

	public function delData() {
		$no_urut=$this->uri->segment(3);
		$this->Model_ci->delete('tb_invoice',array('no_urut'=>$no_urut));
		redirect('invoice');
	}

	public function updateData() {
		$data=array(
			'no_urut'=>$this->input->post('no_urut',true),
			'kode_project'=>$this->input->post('kode_project',true),
			'id_user'=>$this->session->userdata('id_user'),
			'payment_type'=>$this->input->post('payment_type',true),
			'due_date'=>$this->input->post('due_date',true),
			'date'=>$this->input->post('date',true),
			'kepada'=>$this->input->post('kepada',true),
			'note'=>$this->input->post('note',true),
			'is_paid'=>$this->input->post('is_paid',true)
		);
		$this->Model_ci->update('tb_invoice',$data,array('no_invoice'=>$this->input->post('no_invoice',true)));
		$this->session->set_flashdata('alert','Invoice Berhasil Diubah!');
		redirect('invoice');
	}

	public function editData() {
		$no_invoice=$this->input->post('no_invoice',true);
		$data=$this->Model_ci->get_where('tb_invoice',array('no_invoice'=>$no_invoice))->row();
		if (!(strcmp($data->is_paid,"yes"))) {
	      	$paid="selected";
	      	$unpaid="";
		} else {  
		    $paid="";
		    $unpaid="selected";
		}
		?>
		<label>No Invoice</label>
		<input type="text" class="form-control" id="no_invoice" name="no_invoice" value="<?= $data->no_invoice; ?>" readonly>		
		<input type="hidden" name="no_urut" value="<?= $data->no_urut; ?>">
		<label>To</label>
		<input type="text" name="kepada" class="form-control" id="kepada" value="<?= $data->kepada; ?>" required>
		<input type="hidden" name="kode_project" id="kode_project" value="<?= $data->kode_project; ?>">
		<label>Date</label>
		<input type="date" name="date" class="form-control" id="date" value="<?= $data->date; ?>" required>
		<label>For Payment</label>
		<input type="text" name="payment_type" class="form-control" id="payment_type" required value="<?= $data->payment_type; ?>">
		<label>Payment Due Date</label>
		<input type="date" name="due_date" class="form-control" id="due_date" required value="<?= $data->due_date; ?>">	
		<label>Note</label>
		<textarea name="note" class="form-control" rows="3" id="note"><?= $data->note; ?></textarea>
		<label>Payment Status</label>
		<select name="is_paid" class="form-control" id="is_paid">
			<option value="no" <?= $unpaid; ?>>UNPAID</option>
			<option value="yes" <?= $paid; ?>>PAID</option>
		</select>
		<?php
	}

	public function get_item() {
		$no_invoice=$this->input->post('no_invoice',true);
		$data=$this->Model_ci->get_where('tb_item',array('no_invoice'=>$no_invoice));
		foreach ($data->result() as $row) {
			?>
<tr>
	<td><button type="button" class="btn btn-danger btn-small" onclick="delItem('<?= $row->id; ?>')"><i class="fa fa-times"></i></button></td>
	<td><?= $row->item; ?></td>
	<td><?= $row->qty; ?></td>
	<td><?= number_format($row->price); ?></td>
	<td><?= number_format($row->price*$row->qty); ?></td>
</tr>
			<?php
		}
	}

	public function save_item() {
		$data=array(
			'no_invoice' => $this->input->post('no_invoice',true),
			'item' => $this->input->post('item',true),
			'qty' => $this->input->post('qty',true),
			'price' => $this->input->post('price',true)
		);

		$this->Model_ci->insert('tb_item',$data);
		echo "berhasil";
	}

	public function del_item() {
		$id=$this->input->post('id_item',true);
		$this->Model_ci->delete('tb_item',array('id'=>$id));
		echo "berhasil";
	}

	public function autoid(){
			$max=$this->Model_ci->maxdata('no_urut','tb_invoice');
			$result=$max->row();
			$lastid=$result->lastid;
			if (empty($lastid)) {
				$id="00001"; } 
			else {
			$lastid=$lastid+1;
				if (strlen($lastid)=='1') {
					$id="0000".$lastid;
				} else if (strlen($lastid)=='2') {
					$id="000".$lastid;
				} else if
				 (strlen($lastid)=='3') {
					$id="00".$lastid;
				} else if (strlen($id)=='4') {
					$id="0".$lastid;
				} else {
					$id=$lastid;
				}
			}
			return $id;
	}

	public function numberToRoman($number) {
	    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
	    $returnValue = '';
	    while ($number > 0) {
	        foreach ($map as $roman => $int) {
	            if($number >= $int) {
	                $number -= $int;
	                $returnValue .= $roman;
	                break;
	            }
	        }
	    }
	    return $returnValue;
	}

}