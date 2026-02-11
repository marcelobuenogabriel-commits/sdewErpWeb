<div class="modal fade" id="conferencia_pallet" tabindex="-1" aria-labelledby="conferencia_pallet"
        aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ferramentas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <section class="tables" style="padding: 5px !important;">
                <div class="card">
                    <div class="card-header">Criar Pallet</div>
                    <form action="{{ route('conferencia.create_pallet') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <input id="txtNumPrj" name="txtNumPrj" hidden="hidden"></input>
                            <input id="txtCodFpj" name="txtCodFpj" hidden="hidden"></input>
                            <input id="txtOrigemReq" name="txtOrigemReq" hidden="hidden"></input>

                            <div class="row">
                                <div class="col-md-12">
                                    <select class="form-control" id="txtTipPal" name="txtTipPal">
                                        <option value="EL">EL</option>
                                        <option value="P">P</option>
                                        <option value="PG">PG</option>
                                        <option value="RL">RL</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-warning btnCriarPallet">Criar Pallet</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>